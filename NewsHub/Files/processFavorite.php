<?php
    require 'database.php'; //confirm database connection
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
    	die("Request forgery detected");
    }
    $username = $_SESSION['user'];
    
    if (isset($_POST['favoriteButton'])) {
        //if "favorite" is clicked, add to favorites table
        if (strcmp($_POST['favoriteButton'], "Favorite") == 0) {
            $insertFav = $mysqli->prepare("INSERT INTO favorites (user_id, post_id) values (?, ?)");
        
            if (!$insertFav) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
        
            $insertFav->bind_param('ss', $_POST['idUser'], $_POST['idPost']);
            $insertFav->execute();
            $insertFav->close();
            
            //if unfavorite is clicked, delete from favorites table
        } else {
            $deleteFav = $mysqli->prepare("DELETE FROM favorites WHERE user_id=? and post_id=?");
            if (!$deleteFav) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
        
            $deleteFav->bind_param('ss', $_POST['idUser'], $_POST['idPost']);
            $deleteFav->execute();
            $deleteFav->close();
        }
    }
    
    header("Location: mainPage.php");
    exit;
?>