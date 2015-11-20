<?php
    require 'database.php';
    session_start();
    if (isset($_POST['deleteButton'])) {
        $username = $_SESSION['user'];
        $idPost = $_POST['idNum'];
        
        //delete children comments
        $deleteComments = $mysqli->prepare("DELETE FROM comments WHERE post_id=?");
        $deleteComments->bind_param('i', $idPost);
        if (!$deleteComments) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $deleteComments->execute();
        $deleteComments->close();
        
        
        //delete children votes
        $deleteStmt = $mysqli->prepare("DELETE FROM votes WHERE post_id=?");
        $deleteStmt->bind_param('i', $idPost);
        if (!$deleteStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $deleteStmt->execute();
        $deleteStmt->close();
        
           
        //delete children favorites
        $deleteStmt = $mysqli->prepare("DELETE FROM favorites WHERE post_id=?");
        $deleteStmt->bind_param('i', $idPost);
        if (!$deleteStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $deleteStmt->execute();
        $deleteStmt->close();

        //delete post
        $deleteStmt = $mysqli->prepare("DELETE FROM posts WHERE id=?");
        $deleteStmt->bind_param('i', $idPost);
        if (!$deleteStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $deleteStmt->execute();
        $deleteStmt->close();
        header("Location: mainPage.php");
        exit;
    }
?>