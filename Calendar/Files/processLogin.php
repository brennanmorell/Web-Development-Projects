<?php
    require 'database.php';
    header("Content-Type: application/json");
    $username = $_POST['username'];
    $password = $_POST['password'];
    $cryptPassword = crypt($password); //encrypt the password
    
    //Filter input checks
    if(!preg_match('/^[\w_\-]+$/', $username) ){
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid username"
        ));
        exit;
    }
    if(!preg_match('/^[\w_\-]+$/', $password) ){
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid password"
        ));
        exit;
    }
    
    
    if ($_POST['button'] == "Sign up") { //if SIGN UP was clicked...
        //Check if user exists in database
        $doesUserExist = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        if (!$doesUserExist) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
        }
        $doesUserExist->bind_param('s', $username);
        $doesUserExist->execute();
        $doesUserExist->bind_result($cnt);
        $doesUserExist->fetch();
    
        if ($cnt == 1) { //then username is already in table
            echo json_encode(array(
                "success" => false,
                "message" => "Username is taken"
            ));
            $doesUserExist->close();
            exit;
                
        } else { //create user with username/password combination
            $doesUserExist->close();
            $insertUser = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if (!$insertUser) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $insertUser->bind_param('ss', $username, $cryptPassword);
            $insertUser->execute();
            $insertUser->close();
            
            $getSharedCalendars = $mysqli->prepare("SELECT currentUser FROM sharing WHERE sharedUser=?");
            if (!$getSharedCalendars) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
            }
            $getSharedCalendars->bind_param('s', $username);
            $getSharedCalendars->execute();
            $getSharedCalendars->bind_result($accessUser);
            
            $accessUserArr = array();
            $incr = 0;
              
            //get usernames that user has calendar access to
            while ($getSharedCalendars->fetch()) {
                $accessUserArr[$incr] = $accessUser;
                $incr++;
            }
            $getSharedCalendars->close();
            
            ini_set("session.cookie_httponly", 1); //web security check
            session_start();
            $_SESSION['token'] = substr(md5(rand()), 0, 10); // generate a 10-character random string
            $_SESSION['user'] = $username;
            $_SESSION['actualUser'] = $_SESSION['user'];
            echo json_encode(array(
                "success" => true,
                "username" => htmlentities($username),
                "token" => $_SESSION['token'],
                "accessUsers" => $accessUserArr
            ));
            exit;
        }

    } else { //try to sign in with existing account

        //Get user id, password. Then salt and hash the password using crypt
        $doesUserExist = $mysqli->prepare("SELECT COUNT(*), id, password FROM users WHERE username=?");
        $doesUserExist->bind_param('s', $username);
        $doesUserExist->execute();
        $doesUserExist->bind_result($cnt, $user_id, $pwd_hash);
        $doesUserExist->fetch();
        
        if ($cnt == 1 && crypt($password, $pwd_hash) == $pwd_hash) { //then you are signed in
            $doesUserExist->close();
            
            $getSharedCalendars = $mysqli->prepare("SELECT currentUser FROM sharing WHERE sharedUser=?");
            if (!$getSharedCalendars) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
            }
            $getSharedCalendars->bind_param('s', $username);
            $getSharedCalendars->execute();
            $getSharedCalendars->bind_result($accessUser);
            
            $accessUserArr = array();
            $incr = 0;
              
            //get usernames that user has calendar access to
            while ($getSharedCalendars->fetch()) {
                $accessUserArr[$incr] = $accessUser;
                $incr++;
            }
            $getSharedCalendars->close();
            
            
            ini_set("session.cookie_httponly", 1); //web security check
            session_start();
            $_SESSION['token'] = substr(md5(rand()), 0, 10); // generate a 10-character random string
            $_SESSION['user'] = $username;
            $_SESSION['actualUser'] = $_SESSION['user'];
            echo json_encode(array(
                "success" => true,
                "username" => htmlentities($username),
                "token" => $_SESSION['token'],
                "accessUsers" => $accessUserArr
            ));
            exit;
            
        } else { //could not sign in
            echo json_encode(array(
                "success" => false,
                "message" => "Incorrect Username or Password"
            ));
            $doesUserExist->close();
            exit;
        }
     }
?>