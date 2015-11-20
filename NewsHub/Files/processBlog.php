<?php
    require 'database.php'; //confirm database connection
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
    	die("Request forgery detected");
    }
    $username = $_SESSION['user'];
    
    //get id from table (users) and store into value to put into table (posts)
    $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
        if (!$getUserID) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
    $getUserID->bind_param('s', $username);
    $getUserID->execute();
    $getUserID->bind_result($userID);
    $getUserID->fetch();
    $getUserID->close();
        
    
    if (isset($_POST['link'])) {
        $articleURL = $_POST['link'];
    } else {
        $articleURL = "";
    }
    
    if (isset($_POST['description']) && $_POST['description'] != "" && strcmp($_POST['description'], "Post description") != 0) {
        $description = $_POST['description'];
    } else {
        $_SESSION['descErr'] = "Must type description.<br>";
        header("Location: makePost.php");
        exit;
    }
    if (isset($_POST['category'])) {
        if (strcmp($_POST['category'], "other") == 0) {
            if (isset($_POST['otherText'])) {
                $postCategory = $_POST['otherText'];
            } else {
                $postCategory = "other";
            }
        } else {
			
			$postCategory = $_POST['category'];
        }
    } else {
		if(isset($_POST['otherText']))
		{
				$postCategory = $_POST['otherText'];
		}
		else
			$postCategory = "other";
    }
    //insert new post into Module3.posts (database.table)
    $stmt = $mysqli->prepare("INSERT INTO posts (link, description, user_id, category) values (?, ?, ?, ?)");
    
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }

    $stmt->bind_param('ssss', $articleURL, $description, $userID, $postCategory);
    $stmt->execute();
    $stmt->close();
    header("Location: mainPage.php");
    exit;
?>