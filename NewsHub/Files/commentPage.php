<!DOCTYPE html>
    <html>
        <head>
            <title>Comments</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        </head>
        
        <body>
            
            <?php
                require 'database.php';
                session_start();
                
                if (isset($_GET['idPost'])) {
                    $_SESSION['currentPost'] = $_GET['idPost']; //store ID of current post
                    
                }
                $locCurrentPost = $_SESSION['currentPost'];
                if (isset($_SESSION['user'])) { //allow user to post comment
            ?>
                    <!-- Comment form -->
                    <div align="center">
                        <form method="POST" action="processComment.php">
                            <p>
                                <textarea id='comment' name='comment' rows='5' cols='50' placeholder="Write comment"></textarea>
                            </p>
                            <input type="hidden" name="postID" value="<?php echo $_SESSION['currentPost'] ?>" />
                            <input type="submit" name='postComment' value='Share' />
                            &nbsp; &nbsp;
                        </form>
                    </div>
                    
            <?php    
                    
                } else {
                    session_destroy();
                }
                
                //get post information to display at top of this pages
                $getPostInfo = $mysqli->prepare("SELECT link, description, user_id, votes, category FROM posts WHERE id=?");
                if (!$getPostInfo) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $getPostInfo->bind_param('i', $locCurrentPost);
                $getPostInfo->execute();
                $getPostInfo->bind_result($link, $description, $userID, $numVotes, $categoryPost);
                $getPostInfo->fetch();
                $getPostInfo->close();
                
                //get username of user who posted
                $getUsernames = $mysqli->prepare("SELECT username FROM users WHERE id=?");
                if (!$getUsernames) {
                    printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                    exit;
                }
                $getUsernames->bind_param('i', $userID);
                $getUsernames->execute();
                $getUsernames->bind_result($postUser);
                $getUsernames->fetch();
                $getUsernames->close();
            ?>
                
                <form method="GET" action="specificCategory.php">
                    <input type="submit" name="categoryButton" value="<?php echo $categoryPost ?>" />
                </form>
                
                <?php
                    //print out the description of the post
                    if ($yourVotes >= 1 || (!isset($_SESSION['user']))) {
                        print("<form method='POST' action='mainPage.php'>");
                    } else {
                        print ("<form method='POST' action='processVotes.php'>");
                    }
                    
                    print("$description<br>");
                    print("<a href='$link' title='link'>$link</a><br>");
                    ?>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                    <?php
                    print("<input type='submit' name='upVoteButton' value='+' />&nbsp; $numVotes&nbsp;
                          <input type='submit' name='downVoteButton' value='-' /><br>");
            ?>
                    <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>" />
                    <input type="hidden" name="lastPage" value="commentPage.php" />
                    </form>
            <?php
                print("submitted by: <a href='profilePage.php?value_user=$postUser'>$postUser</a><br>");
                if ($_SESSION['user'] == $postUser) {
            ?>
                    <!-- user submitted this post, can edit or delete this post -->
                    <form method="POST" action="editPost.php">
                        <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                        <input type="submit" name="editButton" value="Edit" />
                    </form>
                    <form method="POST" action="deletePost.php">
                        <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                        <input type="submit" name="deleteButton" value="Delete" />
                    </form>
                        
            <?php
                    }
            ?>
                <!-- FAVORITE BUTTON and info -->
                <?php
                    if (isset($_SESSION['user'])) { //show favorite/unfavorite button
                        
                        
                        //if there is a value returned, then this user already favorited this post
                        $getIfFav = $mysqli->prepare("SELECT id FROM favorites WHERE user_id=? and post_id=?");
                        if (!$getIfFav) {
                            printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                            exit;
                        }
                        $getIfFav->bind_param('ii', $userID, $locCurrentPost);
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
                            <input type="hidden" name="idUser" value="<?php echo $userID ?>" />
                            <input type="hidden" name="idPost" value="<?php echo $locCurrentPost ?>" />
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                            <input type="hidden" name="lastPage" value="commentPage.php" />
                            <input type="submit" name="favoriteButton" value="<?php echo $favVal ?>" />
                        </form>
                        <br><br>
            <?php
                        }
                    }
            ?>
                
                
                
            <?php
                //get all comments for specific post
                $getComments = $mysqli->prepare("SELECT id, text, user_id FROM comments WHERE post_id=? ORDER BY id DESC");
                if (!$getComments) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $getComments->bind_param('i', $locCurrentPost);
                $getComments->execute();
                $getComments->bind_result($idNum, $commentText, $userID);
                
                $textArr = array();
                $userIDArr = array();
                $commenterArr = array();
                $commentIDArr = array();
                $incr = 0;

                //fill arrays of corresponding comment text and commenter user id
                while ($getComments->fetch()) {
                    $textArr[$incr] = $commentText;
                    $userIDArr[$incr] = $userID;
                    $commentIDArr[$incr] = $idNum;
                    $incr++;
                }
                $incr = 0;
                $getComments->close();
                
                //Fetching names associated with comments using their user_id from users(id)
                while ($incr < count($userIDArr)) {
                    $getCommenter = $mysqli->prepare("SELECT username FROM users WHERE id=?");
                    if (!$getCommenter) {
                        printf("Query Prep Failed: %s\n", $mysqli->error);
                        exit;
                    }
                    $getCommenter->bind_param('i', $userIDArr[$incr]);
                    $getCommenter->execute();
                    $getCommenter->bind_result($commenterArr[$incr]);
                    $getCommenter->fetch();
                    $getCommenter->close();
                    $incr++;
                }
                
                $incr = 0;
                
                //Printing out all the comments
                while ($incr < count($textArr)) {
                    print("<br>$textArr[$incr]<br>"); //FIXME for cleanup
                    print("submitted by: <a href='profilePage.php?value_user=$commenterArr[$incr]'>$commenterArr[$incr]</a><br>");
                    if (isset($_SESSION['user'])) {
                        if ($_SESSION['user'] == $commenterArr[$incr]) {
            ?>
                            <!-- user posted this comment, display edit and delete comment buttons -->
                            <form method="POST" action="editComment.php">
                                <input type="hidden" name="commentNum" value="<?php echo $commentIDArr[$incr] ?>" />
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                                <input type="submit" name="editButton" value="Edit" />
                            </form>
                            <form method="POST" action="deleteComment.php">
                                <input type="hidden" name="commentNum" value="<?php echo $commentIDArr[$incr] ?>" />
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                                <input type="submit" name="deleteButton" value="Delete" />
                            </form>
            <?php
                        }
                    }
                    print("&nbsp;&nbsp;");
                    $incr++;
                }
            ?>
            
            <!-- back to main page -->
            <form action="mainPage.php">
                <input type="submit" name='backToMain' value="Back" />
            </form>

        </body>
    </html>