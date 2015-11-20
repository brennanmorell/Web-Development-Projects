<!DOCTYPE html>
    <html>
        <head>
            <title>NewsHub</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        
            
        </head>
        
        <body>
            
        <?php
            require 'database.php';
            session_start();
            $_SESSION['user'];
            if (!isset($_SESSION['user'])) { //usernot not set, you are NOT loggeed in
                session_destroy(); //kill session if you are not signed in
        ?>
                
                    <form method="POST" action="processLogin.php">
                        <!-- user sign in or sign up -->
                        <div class='row right'>
                            <div class="col-sm-2">
                                <input id="existingUser" type="text" class='form-control' placeholder="username" name="user"/>
                            </div>
                            <div class="col-sm-2">
                                <input id="password" type="password" placeholder="password" class='form-control' name="password" />
                            </div>
                            <div class="col-xs-1">
                                <input type="submit" name="loginButton" class="btn btn-default btn-s" value="Sign in" />
                            </div>
                            <div class="col-xs-1">
                                <input type="submit" name="signUpButton" class="btn btn-default btn-s" value="Sign up" />
                            </div>
                        </div>
                    </form>
        <?php
            } else { //username is set, you are logged in
                $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
                if (!$getUserID) {
                    printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                    exit;
                }
                $getUserID->bind_param('s', $_SESSION['user']);
                $getUserID->execute();
                $getUserID->bind_result($currentID);
                $getUserID->fetch();
                $getUserID->close();
                
                $thisUser = $_SESSION['user'];
        ?>
                <div align="right">
                    <?php print("<a href='profilePage.php?value_user=$thisUser'>$thisUser</a><br>"); ?>
                    <!-- user log out -->
                    <form method="POST" action="processLogout.php">
                        <input type="submit" name="logoutButton" class="btn btn-default btn-s" value="Log out" />
                    </form>
                </div>
                
                <!-- Make a post -->
                <form method="POST" action="makePost.php">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                    <input type="submit" name="makePostButton" class="btn btn-default btn-s" value="Make Post" />
                </form>
        <?php
            }
        ?>

            
            <!-- Display the posts -->
            <h2 align="center">NewsHub</h2>
            
            <?php
                //get description, link, and username for a post
                $getPosts = $mysqli->prepare("SELECT id, link, description, user_id, votes, category FROM posts ORDER BY id DESC");
                if (!$getPosts) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $getPosts->execute();
                $getPosts->bind_result($idNum, $link, $description, $userID, $numVotes, $categoryPost);
                
                $postIDArr = array();
                $descArr = array();
                $linkArr = array();
                $posterArr = array();
                $votesArr = array();
                $categoryArr = array();
                $incr = 0;
                
                //keep displaying posts until there are no more
                while ($getPosts->fetch()) {
                    $postIDArr[$incr] = $idNum;
                    $descArr[$incr] = $description;
                    $linkArr[$incr] = $link;
                    $IDArr[$incr] = $userID;
                    $votesArr[$incr] = $numVotes;
                    $categoryArr[$incr] = $categoryPost;
                    $incr++;
                }
                $incr = 0;
                $getPosts->close();
                
                while ($incr < count($descArr)) {
                    $getVoteStatus = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE user_id=? and post_id=?");
                    if (!$getVoteStatus) {
                        printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                        exit;
                    }
                    $getVoteStatus->bind_param('ii', $currentID, $postIDArr[$incr]);
                    $getVoteStatus->execute();
                    $getVoteStatus->bind_result($yourVotes);
                    $getVoteStatus->fetch();
                    $getVoteStatus->close();
                    
                    
            ?>
                    <!-- click category button to view all posts within this category -->
                    <br>
                    <form method="GET" action="specificCategory.php">
                        <input type="submit" name="categoryButton" class="btn btn-default btn-xs" value="<?php echo $categoryArr[$incr] ?>" />
                    </form>
            <?php
                    //print out the description of the post
                    if ($yourVotes >= 1 || (!isset($_SESSION['user']))) {
                        print("<form method='POST' action='mainPage.php'>");
                    } else {
                        print ("<form method='POST' action='processVotes.php'>");
                    }
                    
                    print("$descArr[$incr]<br>");
                    print("<a href='$linkArr[$incr]' title='link'>$linkArr[$incr]</a><br>");
                    print("<input type='submit' name='upVoteButton' class='btn btn-default btn-xs' value='+' />&nbsp; $votesArr[$incr]&nbsp;
                          <input type='submit' name='downVoteButton' class='btn btn-default btn-xs' value='-' /><br>");
            ?>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>" />
                    <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                    </form>
                    
                    
            <?php
                    //get username associated with specific ID
                    $getUsernames = $mysqli->prepare("SELECT username FROM users WHERE id=?");
                    if (!$getUsernames) {
                        printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                        exit;
                    }
                    $getUsernames->bind_param('i', $IDArr[$incr]);
                    $getUsernames->execute();
                    $getUsernames->bind_result($postUser);
                    $getUsernames->fetch();
                    $getUsernames->close();
                    
                    
                    //link to take you to user profile
                    print("submitted by: <a href='profilePage.php?value_user=$postUser'>$postUser</a><br>");
                    
                    
                    
                    if ($_SESSION['user'] == $postUser) {
            ?>
                        <!-- user submitted this post, can edit or delete this post -->
                        <form method="POST" action="editPost.php">
                            <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                            <input type="submit" name="editButton" class="btn btn-default btn-xs" value="Edit" />
                        </form>
                        <form method="POST" action="deletePost.php">
                            <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                            <input type="submit" name="deleteButton" class="btn btn-default btn-xs" value="Delete" />
                        </form>
                        
            <?php
                    }
            ?>
            
                <!-- Comments button for each post -->
                <form method="GET" action="commentPage.php">
                    <input type="hidden" name="idPost" value="<?php echo $postIDArr[$incr] ?>" />
                    <input type="submit" name="commentButton" class="btn btn-default btn-xs" value="Comments" />
                    <br>
                </form>
                
                
                <!-- FAVORITE BUTTON and info -->
                <?php
                    if (isset($_SESSION['user'])) { //show favorite/unfavorite button
                        
                        
                        //if there is a value returned, then this user already favorited this post
                        $getIfFav = $mysqli->prepare("SELECT id FROM favorites WHERE user_id=? and post_id=?");
                        if (!$getIfFav) {
                            printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                            exit;
                        }
                        $getIfFav->bind_param('ss', $currentID, $postIDArr[$incr]);
                        $getIfFav->execute();
                        $getIfFav->bind_result($favID);
                        $getIfFav->fetch();
                        $getIfFav->close();
                        
                        if ($favID <= 0) { //check if favorited already, then display proper text
                            $favVal = "Favorite";
                        } else {
                            $favVal = "Unfavorite";
                        }
                  
                  
                        if ($currentID != $IDArr[$incr]) {      
                ?>
                        <form method="POST" action="processFavorite.php">
                            <input type="hidden" name="idUser" value="<?php echo $currentID ?>" />
                            <input type="hidden" name="idPost" value="<?php echo $postIDArr[$incr] ?>" />
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                            <input type="hidden" name="lastPage" value="mainPage.php" />
                            <input type="submit" name="favoriteButton" class='btn btn-default btn-xs' value="<?php echo $favVal ?>" />
                        </form>
                        <br><br>



            <?php
                        }
                    }
                    $incr++;
                }
            ?>
                 
        </body>
    </html>