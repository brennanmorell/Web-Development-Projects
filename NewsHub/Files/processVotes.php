<?php
    require 'database.php';
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
    	die("Request forgery detected");
    }
    $currentUser = $_SESSION['user'];
    
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
    
    $idPost = $_POST['idNum']; //id of the post
    
    $editStmt = $mysqli->prepare("SELECT votes FROM posts WHERE id=?");
        $editStmt->bind_param('i', $idPost);
        if (!$editStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $editStmt->execute();
        $editStmt->bind_result($currentVotes);
        $editStmt->fetch();
        $editStmt->close();
        
        if(isset($_POST['upVoteButton']))
        {
            //increase the votes for this post
            $currentVotes++;
            $updateStmt = $mysqli->prepare("UPDATE posts SET votes=? WHERE id=?");
            $updateStmt->bind_param('ii', $currentVotes, $idPost);
            if (!$updateStmt) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $updateStmt->execute();
            $updateStmt->close();
    
            //save record of vote so they can't cast another one
            $saveVote = $mysqli->prepare("INSERT INTO votes (post_id, user_id, direction) values (?, ?, ?)");
            $voteDirection = 1;
            $saveVote->bind_param('iii', $idPost, $userID, $voteDirection);
           
            if (!$saveVote) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }

            
            $saveVote->execute();
            $saveVote->close();
            
            header("Location: mainPage.php");
            exit;
        }
        else
        {
            //decrease the votes for this post
            $currentVotes--;
            
            if($currentVotes > -3) {
                $updateStmt = $mysqli->prepare("UPDATE posts SET votes=? WHERE id=?");
                $updateStmt->bind_param('ii', $currentVotes, $idPost);
                if (!$updateStmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $updateStmt->execute();
                $updateStmt->close();
                
                //save record of vote so they can't cast another one
                $saveVote = $mysqli->prepare("INSERT INTO votes (post_id, user_id, direction) values (?, ?, ?)");
                $voteDirection = 1;
                $saveVote->bind_param('iii', $idPost, $userID, $voteDirection);
               
                if (!$saveVote) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
    
                $saveVote->execute();
                $saveVote->close();
            }
            else
            {
                //votes have reached -3 so delete it (first delete child comments)
                $deleteStmt = $mysqli->prepare("DELETE FROM comments WHERE post_id=?");
                $deleteStmt->bind_param('i', $idPost);
                if (!$deleteStmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $deleteStmt->execute();
                $deleteStmt->close();
                
                //delete child votes
                $deleteStmt = $mysqli->prepare("DELETE FROM votes WHERE post_id=?");
                $deleteStmt->bind_param('i', $idPost);
                if (!$deleteStmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $deleteStmt->execute();
                $deleteStmt->close();
                
                
                //now delete the post itself
                $deleteStmt = $mysqli->prepare("DELETE FROM posts WHERE id=?");
                $deleteStmt->bind_param('i', $idPost);
                if (!$deleteStmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $deleteStmt->execute();
                $deleteStmt->close();
            }
            
            header("Location: mainPage.php");
            exit;
        }
?>

