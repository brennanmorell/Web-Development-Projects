<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    $username = $_SESSION['user'];
    
    $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
    if (!$getUserID) { //mysql error
        $jsonFailureData = array();
        $jsonFailureData["success"] = false;
        $jsonFailureData["message"] = $mysqli->error;
        echo json_encode($jsonFailureData);
        exit;
    }
    
    $getUserID->bind_param('s', $username);
    $getUserID->execute();
    $getUserID->bind_result($userID);
    $getUserID->fetch();
    $getUserID->close();
    
    $getCategories = $mysqli->prepare("SELECT category FROM events WHERE user_id=?");
    if (!$getCategories) { //mysql error
        $jsonFailureData = array();
        $jsonFailureData["success"] = false;
        $jsonFailureData["message"] = $mysqli->error;
        echo json_encode($jsonFailureData);
        exit;
    }
    
    $getCategories->bind_param('i', $userID);
    $getCategories->execute();
    $getCategories->bind_result($category);

    
    //store information in arrays
    $categoryArr = array();
    
    $incr = 0;

    //fetch each piece of data from table, insert into arrays
    while ($getCategories->fetch()) {
        $categoryArr[$incr] = $category;
        $incr++;
    }
    $getCategories->close();
    
    $categoryArr = array_unique($categoryArr); //remove duplicates
    $categoryArr = array_values($categoryArr); //fixes array indices since array_unique changes them
    
    //json array to send back with all information
    $jsonSuccessData = array();
    $jsonSuccessData["success"] = true;
    $jsonSuccessData["categories"] = $categoryArr;
    echo json_encode($jsonSuccessData);
    exit;
    
?>