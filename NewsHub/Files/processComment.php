<?php
    require 'database.php';
    session_start();
    
    $comment = $_POST['comment'];
    $currentUser = $_SESSION['user'];
    $currentPost = $_SESSION['currentPost'];
    
    //get id of current user
    $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
        if (!$getUserID) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
    $getUserID->bind_param('s', $currentUser);
    $getUserID->execute();
    $getUserID->bind_result($userID);
    $getUserID->fetch();
    $getUserID->close();
    
    //write comment to database table
    $saveComment = $mysqli->prepare("INSERT INTO comments (text, post_id, user_id) VALUES (?,?,?)");
        if (!$saveComment) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        
    $saveComment->bind_param("sii", $comment, $currentPost, $userID);
    $saveComment->execute();
    $saveComment->close();
    
    //redirect back to comment page
    header("Location: commentPage.php");
    exit;
    
    

?>
