<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    $username = $_SESSION['user'];
    $clickedCategory = $_POST['category'];
    
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
    
    
    $getIDs = $mysqli->prepare("SELECT id FROM events WHERE user_id=? and category=?");
    if (!$getIDs) { //mysql error
        $jsonFailureData = array();
        $jsonFailureData["success"] = false;
        $jsonFailureData["message"] = $mysqli->error;
        echo json_encode($jsonFailureData);
        exit;
    }
    
    $getIDs->bind_param('is', $userID, $clickedCategory);
    $getIDs->execute();
    $getIDs->bind_result($eventID);

    
    //store information in arrays
    $eventIDArr = array();
    
    $incr = 0;

    //fetch each piece of data from table, insert into arrays
    while ($getIDs->fetch()) {
        $eventIDArr[$incr] = $eventID;
        $incr++;
    }
    $getIDs->close();
    
    $jsonSuccessData = array();
    $jsonSuccessData["success"] = true;
    $jsonSuccessData["eventsArr"] = $eventIDArr;
    echo json_encode($jsonSuccessData);
    exit;
    
    
?>