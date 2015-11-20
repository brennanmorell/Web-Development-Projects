<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security check
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    $thisUser = $_SESSION['user'];
    
    //get user id for use with events table
    $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
    if (!$getUserID) { //mysql error
        $jsonFailureData = array();
        $jsonFailureData["success"] = false;
        $jsonFailureData["message"] = $mysqli->error;
        echo json_encode($jsonFailureData);
        exit;
    }
    $getUserID->bind_param('s', $thisUser);
    $getUserID->execute();
    $getUserID->bind_result($userID); //current user ID
    $getUserID->fetch();
    $getUserID->close();
    
    
    //get events to send back as json data
    $getEvents = $mysqli->prepare("SELECT id, date, start, end, category, description FROM events WHERE user_id=?");
    if (!$getEvents) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
    }
    $getEvents->bind_param('i', $userID);
    $getEvents->execute();
    $getEvents->bind_result($eID, $eDate, $eStart, $eEnd, $eCategory, $eDesc);
    
    //store information in arrays
    $IDArr = array();
    $dateArr = array();
    $startArr = array();
    $endArr = array();
    $categoryArr = array();
    $descriptionArr = array();
    $incr = 0;
    
    //fetch each piece of data from table, insert into arrays
    while ($getEvents->fetch()) {
        $IDArr[$incr] = $eID;
        $dateArr[$incr] = $eDate;
        $startArr[$incr] = $eStart;
        $endArr[$incr] = $eEnd;
        $categoryArr[$incr] = $eCategory;
        $descriptionArr[$incr] = $eDesc;
        $incr++;
    }
    $getEvents->close();
    
    //json array to send back with all information
    $jsonSuccessData = array();
    $jsonSuccessData["success"] = true;
    $jsonSuccessData["ids"] = $IDArr;
    $jsonSuccessData["dates"] = $dateArr;
    $jsonSuccessData["starts"] = $startArr;
    $jsonSuccessData["ends"] = $endArr;
    $jsonSuccessData["categories"] = $categoryArr;
    $jsonSuccessData["descriptions"] = $descriptionArr;
    echo json_encode($jsonSuccessData);
    exit;
      
?>