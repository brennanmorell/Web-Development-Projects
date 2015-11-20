<?php
    require 'database.php';
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
    	die("Request forgery detected");
    }
    if (isset($_POST['deleteButton'])) {
        $username = $_SESSION['user'];
        $idComment = $_POST['commentNum'];
        $deleteStmt = $mysqli->prepare("DELETE FROM comments WHERE id=?");
        $deleteStmt->bind_param('i', $idComment);
        if (!$deleteStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $deleteStmt->execute();
        $deleteStmt->close();
        header("Location: commentPage.php");
        exit;
    }
?>