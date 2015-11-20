<?php
    require 'database.php';
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
    	die("Request forgery detected");
    }
    if (isset($_POST['editPostButton'])) {
        //update column's row entry with new description and link from edited post
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
            $postCategory = "other";
        }
        if (!isset($_POST['link'])) {
            $_POST['link'] = "";
        }
        if (isset($_POST['description']) && $_POST['description'] != "" && strcmp($_POST['description'], "Post description") != 0) {
            $updateStmt = $mysqli->prepare("UPDATE posts SET description=?, link=?, category=? WHERE id=?");
            $updateStmt->bind_param('sssi', $_POST['description'], $_POST['link'], $postCategory, $_SESSION['idPost']);
            if (!$updateStmt) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $updateStmt->execute();
            $updateStmt->close();
    
            header("Location: mainPage.php");
            exit;
        } else {
            $_SESSION['descErr'] = "Must type description.<br>";
            header("Location: editPost.php");
            exit;
        }
    }
?>