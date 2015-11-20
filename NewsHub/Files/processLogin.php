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

     //Get user id, password. Then salt and hash the password using crypt
     $doesUserExist = $mysqli->prepare("SELECT COUNT(*), id, password FROM users WHERE username=?");
     $doesUserExist->bind_param('s', $username);
     $doesUserExist->execute();
     $doesUserExist->bind_result($cnt, $user_id, $pwd_hash);
     $doesUserExist->fetch();
     
     if ($cnt == 1 && crypt($password, $pwd_hash) == $pwd_hash) { //then you are signed in
         $doesUserExist->close();
         
         ini_set("session.cookie_httponly", 1); //web security check
         session_start();
         $_SESSION['token'] = substr(md5(rand()), 0, 10); // generate a 10-character random string
         echo json_encode(array(
             "success" => true,
             "username" => htmlentities($username),
             "token" => $_SESSION['token']
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