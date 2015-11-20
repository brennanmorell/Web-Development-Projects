<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    //store all event information in variables
    $eventDesc = $_POST['eventDesc'];
    $eventDate = $_POST['eventDate'];
    $eventStart = $_POST['eventStart'];
    $eventEnd = $_POST['eventEnd'];
    $eventCat = $_POST['eventCat'];
    $thisUser = $_POST['currentUser'];
    $groupedArr = $_POST['grouping'];
    $splitGroup = explode(",", $groupedArr);
    //$thisUser = $_SESSION['user']; //should work, try later
    
    
    //Regex for date format: MM/DD/YYYY
    if(!preg_match('~^(0[1-9]|1[0-2])[/](0[1-9]|[12][0-9]|3[01])[/]20[0-9][0-9]~', $eventDate) ){
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid date"
        ));
        exit;
        
    //Regex for time format: HH:MMPM || HH::MMAM
    } else if (!preg_match('~^\b([0-9]|0[0-9]|1[0-2]):[0-5][0-9](PM|AM)\b~i', $eventStart) ) {
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid time"
        ));
        exit;
        
    //Regex for time format: HH:MMPM || HH::MMAM
    } else if (!preg_match('~^\b([0-9]|0[0-9]|1[0-2]):[0-5][0-9](PM|AM)\b~i', $eventEnd) ) {
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid time"
        ));
        exit;
        
    } else { //correct input, can put into table now
        
        if (count($splitGroup) > 0 && $splitGroup[0] != "") {
            //error checking. determine if these users exist
            for ($i = 0; $i < count($splitGroup); $i++) {
                $checkSharedExists = $mysqli->prepare("SELECT id FROM users WHERE username=?");
                if (!$checkSharedExists) {
                 echo json_encode(array(
                     "success" => false,
                     "message" => $mysqli->error
                 ));
                 exit;
                 }
                
                 $checkSharedExists->bind_param('s', $splitGroup[$i]);
                 $checkSharedExists->execute();
                 $checkSharedExists->bind_result($returnedID);
                 $checkSharedExists->fetch();
                 $checkSharedExists->close();
                 
                 
                 if (empty($returnedID) || $checkSharedExists -> num_rows > 0) {
                    echo json_encode(array(
                        "success" => false,
                        "message" => "One of these group usernames does not exist."
                    ));
                    exit;
                 }
            }
            
        }
        
        
        //Get the ID of the user who is inputting an event
        $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
        if (!$getUserID) {
            echo json_encode(array(
                "success" => false,
                "message" => $mysqli->error
            ));
            exit;
        }
        $getUserID->bind_param('s', $thisUser);
        $getUserID->execute();
        $getUserID->bind_result($userID);
        $getUserID->fetch();
        $getUserID->close();
        
        //Add event to table -- date, start, end, category, description, and the userID from above
        $addEventStmt = $mysqli->prepare("INSERT INTO events (date, start, end, category, description, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$addEventStmt) {
            echo json_encode(array(
                "success" => false,
                "message" => $mysqli->error
            ));
            exit;
        }
        $addEventStmt->bind_param('sssssi', $eventDate, $eventStart, $eventEnd, $eventCat, $eventDesc, $userID);
        $addEventStmt->execute();
        $addEventStmt->close();
        

        if (count($splitGroup) > 0 && $splitGroup[0] != "") { //then there's a group event
            
            //GET MOST RECENT ENTRY'S ID
            $getEvents = $mysqli->prepare("SELECT MAX(id) FROM events");
            if (!$getEvents) {
                echo json_encode(array(
                    "success" => false,
                    "message" => $mysqli->error
                ));
                exit;
            }
            $getEvents->execute();
            $getEvents->bind_result($mostRecentEntryID);
            $getEvents->fetch();
            $getEvents->close();
            
            // SET GROUP ID OF MOST RECENT ENTRY
            $updateStmt = $mysqli->prepare("UPDATE events SET groupID=? WHERE id=?");
            $updateStmt->bind_param('ii', $mostRecentEntryID, $mostRecentEntryID);
            if (!$updateStmt) {
                $jsonFailureData = array();
                $jsonFailureData["success"] = false;
                $jsonFailureData["message"] = $mysqli->error;
                echo json_encode($jsonFailureData);
                exit;
            }
            $updateStmt->execute();
            $updateStmt->close();
            

            
            //ENTER EVENT FOR ALL OTHER USERS
            for ($i = 0; $i < count($splitGroup); $i++) {

                //GET USER ID FOR EACH GROUP USER
                $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
                if (!$getUserID) {
                    echo json_encode(array(
                        "success" => false,
                        "message" => $mysqli->error
                    ));
                    exit;
                }

                $getUserID->bind_param('s', $splitGroup[$i]);
                $getUserID->execute();
                $getUserID->bind_result($groupUserID);
                $getUserID->fetch();
                $getUserID->close();
                

                
                //INSERT EVENT FOR ALL USERS
                $addEventStmt = $mysqli->prepare("INSERT INTO events (date, start, end, category, description, user_id, groupID) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if (!$addEventStmt) {
                    echo json_encode(array(
                        "success" => false,
                        "message" => $mysqli->error
                    ));
                    exit;
                }
                $addEventStmt->bind_param('sssssii', $eventDate, $eventStart, $eventEnd, $eventCat, $eventDesc, $groupUserID, $mostRecentEntryID);
                $addEventStmt->execute();
                $addEventStmt->close();
            }
        }
    }
    
    echo json_encode(array(
        "success" => true
    ));
    exit;
        
?>