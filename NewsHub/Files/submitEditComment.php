<?php
    require 'database.php';
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
    	die("Request forgery detected");
    }
    if (isset($_POST['editCommentButton'])) {
        //update column's row entry with new edited comment
        $updateStmt = $mysqli->prepare("UPDATE comments SET text=? WHERE id=?");
        $updateStmt->bind_param('si', $_POST['comment'], $_SESSION['idComment']);
        if (!$updateStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $updateStmt->execute();
        $updateStmt->close();

        header("Location: commentPage.php");
        exit;
    }
?>