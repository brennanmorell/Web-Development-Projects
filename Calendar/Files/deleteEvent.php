<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    //store all event information in variables
    $eventID = $_POST['eventID'];
     
     
    $deleteEvent = $mysqli->prepare("DELETE FROM events WHERE id=?");
    if (!$deleteEvent) { //mysql error
        $jsonFailureData = array();
        $jsonFailureData["success"] = false;
        $jsonFailureData["message"] = $mysqli->error;
        echo json_encode($jsonFailureData);
        exit;
    }
    
    $deleteEvent->bind_param('i', $eventID);
    $deleteEvent->execute();
    $deleteEvent->close();
    
   
    echo json_encode(array(
        "success" => true,
    ));
    exit;
?>