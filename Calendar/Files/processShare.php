<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    $username = $_SESSION['user'];
    $sharedUsername = $_POST['sharedName'];
    
    //error checking. determien if a sharing user actually exists
    $checkSharedExists = $mysqli->prepare("SELECT id FROM users WHERE username=?");
    if (!$checkSharedExists) {
     echo json_encode(array(
         "success" => false,
         "message" => $mysqli->error
     ));
     exit;
     }
    
     $checkSharedExists->bind_param('s', $sharedUsername);
     $checkSharedExists->execute();
     $checkSharedExists->bind_result($returnedID);
     $checkSharedExists->fetch();
     $checkSharedExists->close();
     
     if (empty($returnedID) || $checkSharedExists -> num_rows > 0) {
        echo json_encode(array(
            "success" => false,
            "message" => "Username does not exist."
        ));
        exit;
     }
    
    
    $insertUser = $mysqli->prepare("INSERT INTO sharing (sharedUser, currentUser) VALUES (?, ?)");
    if (!$insertUser) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $insertUser->bind_param('ss', $sharedUsername, $username);
    $insertUser->execute();
    $insertUser->close();
    
    echo json_encode(array(
        "success" => true
    ));
    exit;
?>