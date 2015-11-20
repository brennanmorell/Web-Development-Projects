<?php
    require 'database.php';
    header("Content-Type: application/json");
    ini_set("session.cookie_httponly", 1); //web security
    session_start();
    
    if($_SESSION['token'] !== $_POST['token']){
        die("Request forgery detected");
    }
    
    if ($_SESSION['user'] == $_POST['editUser']) {
        $eventID = $_POST['eventID'];
        $editDesc = $_POST['editDesc'];
        $editDate = $_POST['editDate'];
        $editStart = $_POST['editStart'];
        $editEnd = $_POST['editEnd'];
        $editCat = $_POST['editCat'];
        
        //Regex for date format: MM/DD/YYYY
        if(!preg_match('~^(0[1-9]|1[0-2])[/](0[1-9]|[12][0-9]|3[01])[/]20[0-9][0-9]~', $editDate) ){
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid date"
            ));
            exit;
            
        //Regex for time format: HH:MMPM || HH::MMAM
        } else if (!preg_match('~^\b([0-9]|0[0-9]|1[0-2]):[0-5][0-9](PM|AM)\b~i', $editStart) ) {
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid time"
            ));
            exit;
            
        //Regex for time format: HH:MMPM || HH::MMAM
        } else if (!preg_match('~^\b([0-9]|0[0-9]|1[0-2]):[0-5][0-9](PM|AM)\b~i', $editEnd) ) {
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid time"
            ));
            exit;
            
        } else { //correct input, can put into table now
            
            $checkGroup = $mysqli->prepare("SELECT groupID FROM events WHERE id=?");
            if (!$checkGroup) {
                echo json_encode(array(
                    "success" => false,
                    "message" => $mysqli->error
                ));
                exit;
            }

            $checkGroup->bind_param('i', $eventID);
            $checkGroup->execute();
            $checkGroup->bind_result($groupUserID);
            $checkGroup->fetch();
            $checkGroup->close();
            
            if ($groupUserID !== NULL) {
                $updateStmt = $mysqli->prepare("UPDATE events SET date=?, start=?, end=?, category=?, description=? WHERE groupID=?");
                $updateStmt->bind_param('sssssi', $editDate, $editStart, $editEnd, $editCat, $editDesc, $groupUserID);
                if (!$updateStmt) {
                    $jsonFailureData = array();
                    $jsonFailureData["success"] = false;
                    $jsonFailureData["message"] = $mysqli->error;
                    echo json_encode($jsonFailureData);
                    exit;
                }
                $updateStmt->execute();
                $updateStmt->close();
                echo json_encode(array(
                    "success" => true
                ));
                exit;
            } else {
            
                $updateStmt = $mysqli->prepare("UPDATE events SET date=?, start=?, end=?, category=?, description=? WHERE id=?");
                $updateStmt->bind_param('sssssi', $editDate, $editStart, $editEnd, $editCat, $editDesc, $eventID);
                if (!$updateStmt) {
                    $jsonFailureData = array();
                    $jsonFailureData["success"] = false;
                    $jsonFailureData["message"] = $mysqli->error;
                    echo json_encode($jsonFailureData);
                    exit;
                }
                $updateStmt->execute();
                $updateStmt->close();
                
                echo json_encode(array(
                    "success" => true
                ));
                exit;
            }
        }
        
        
    } else {
        print("Error bad user."); //improper user trying to edit someone else's event
    }
    
?>