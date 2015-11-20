<?php
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security check
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    unset($_SESSION['user']);
    session_destroy(); //log out
    echo json_encode(array(
        "success" => true
    ));
    exit;
?>