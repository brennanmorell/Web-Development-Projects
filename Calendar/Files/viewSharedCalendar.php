<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    $sharedUsername = $_POST['sharedName'];
    $_SESSION['user'] = $sharedUsername;
    
    echo json_encode(array(
        "success" => true,
        "actualUser" => $_SESSION['actualUser'],
        "sharedUser" => $_SESSION['user']
    ));
    exit;
    
?>