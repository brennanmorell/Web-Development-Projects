<!DOCTYPE html>
    <html>
        <head>
            <title>Profile Page</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        </head>
        
        <body>
            <!-- back to main page -->
            <form action="mainPage.php">
                <input type="submit" name='backToMain' value="Main Page" />
            </form>
            
            <?php
                require 'database.php';
                session_start();
                $userProfileName = $_GET['value_user'];
                
                $getUserID = $mysqli->prepare("SELECT id FROM users WHERE username=?");
                if (!$getUserID) {
                    printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                    exit;
                }
                $getUserID->bind_param('s', $userProfileName);
                $getUserID->execute();
                $getUserID->bind_result($profileID);
                $getUserID->fetch();
                $getUserID->close();
                
            ?>
            
            <h2><?php echo $userProfileName ?></h2>
            
            <!-- display all posts created by this user -->
            <h3>Posts</h3>
            
            <?php
                //get description, link, and username for a post
                $getPosts = $mysqli->prepare("SELECT id, link, description, votes, category FROM posts WHERE user_id=? ORDER BY id DESC");
                if (!$getPosts) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $getPosts->bind_param('i', $profileID);
                $getPosts->execute();
                $getPosts->bind_result($idNum, $link, $description, $numVotes, $categoryPost);
                
                $postIDArr = array();
                $descArr = array();
                $linkArr = array();
                $votesArr = array();
                $categoryArr = array();
                $incr = 0;
                
                //keep displaying posts until there are no more
                while ($getPosts->fetch()) {
                    $postIDArr[$incr] = $idNum;
                    $descArr[$incr] = $description;
                    $linkArr[$incr] = $link;
                    $votesArr[$incr] = $numVotes;
                    $categoryArr[$incr] = $categoryPost;
                    $incr++;
                }
                $getPosts->close();
                $incr = 0;
                
                while ($incr < count($descArr)) {
                    $getVoteStatus = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE user_id=? and post_id=?");
                    if (!$getVoteStatus) {
                        printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                        exit;
                    }
                    $getVoteStatus->bind_param('ii', $profileID, $postIDArr[$incr]);
                    $getVoteStatus->execute();
                    $getVoteStatus->bind_result($yourVotes);
                    $getVoteStatus->fetch();
                    $getVoteStatus->close();
            ?>
            
            
            
            <!-- click category button to view all posts within this category -->
                    <form method="GET" action="specificCategory.php">
                        <input type="submit" name="categoryButton" value="<?php echo $categoryArr[$incr] ?>" />
                    </form>
            <?php
                    //print out the description of the post
                    if ($yourVotes >= 1) {
                        print("<form method='POST' action='mainPage.php'>");
                    } else {
                        print ("<form method='POST' action='processVotes.php'>");
                    }
                    
                    print("$descArr[$incr]<br>");
                    print("<a href='$linkArr[$incr]' title='link'>$linkArr[$incr]</a><br>");
                    print("<input type='submit' name='upVoteButton' value='+' />&nbsp; $votesArr[$incr]&nbsp;
                          <input type='submit' name='downVoteButton' value='-' /><br>");
            ?>
                    <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>" />
                    <input type="hidden" name="lastPage" value="profilePage.php" />
                    </form>
                    
            <?php
                                                           
                    if (strcmp($_SESSION['user'], $userProfileName) == 0) {
            ?>
                        <!-- user submitted this post, can edit or delete this post -->
                        <form method="POST" action="editPost.php">
                            <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                            <input type="submit" name="editButton" value="Edit" />
                        </form>
                        <form method="POST" action="deletePost.php">
                            <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                            <input type="submit" name="deleteButton" value="Delete" />
                        </form>
                        
            <?php
                    }
            ?>
            
                <!-- Comments button for each post -->
                <form method="GET" action="commentPage.php">
                    <input type="hidden" name="idPost" value="<?php echo $postIDArr[$incr] ?>" />
                    <input type="submit" name="commentButton" value="Comments" />
                    <br>
                </form>
                
                
            <?php
                    $incr++;
                }
            ?>
            
            
            
            
            
            
            
            
            <!--------------- display all of the posts that a user favorited ------------>
            <h3>Favorites</h3>
            
            <?php
                $getFavs = $mysqli->prepare("SELECT post_id FROM favorites WHERE user_id=?");
                if (!$getFavs) {
                            printf("Query Prep Failed Usernames1: %s\n", $mysqli->error);
                            exit;
                        }
                $getFavs->bind_param('i', $profileID);
                $getFavs->execute();
                $getFavs->bind_result($favoritePost);
                
                $favArr = array();
                $incr = 0;
                while ($getFavs->fetch()) {
                    $favArr[$incr] = $favoritePost;
                }
                $getFavs->close();
                $incr = 0;
                
                //Get all information for each of the favorited posts of this user
                $getFavPosts = $mysqli->prepare("SELECT description, link, user_id, votes, category FROM posts WHERE id=?");
                if (!$getFavPosts) {
                        printf("Query Prep Failed Usernames2: %s\n", $mysqli->error);
                        exit;
                    }
                $getFavPosts->bind_param('i', $favArr[$incr]);
                $getFavPosts->execute();
                $getFavPosts->bind_result($favPostDesc, $favPostLink, $favPostUser, $favPostVote, $favPostCat);
                
                while ($getFavPosts->fetch()) {
                    $favDescArr[$incr] = $favPostDesc;
                    $favLinkArr[$incr] = $favPostLink;
                    $favUserArr[$incr] = $favPostUser;
                    $favVoteArr[$incr] = $favPostVote;
                    $favCatArr[$incr] = $favPostCat;
                    $incr++;
                }
                $getFavPosts->close();
                $incr = 0;
                    
                    
                    
                    if (count($favDescArr) >= 1) {
                    while ($incr < count($favArr)) {
                    $getVoteStatus = $mysqli->prepare("SELECT COUNT(*) FROM votes WHERE user_id=? and post_id=?");
                    if (!$getVoteStatus) {
                        printf("Query Prep Failed Usernames3: %s\n", $mysqli->error);
                        exit;
                    }
                    $getVoteStatus->bind_param('ii', $favUserArr[$incr], $favArr[$incr]);
                    $getVoteStatus->execute();
                    $getVoteStatus->bind_result($yourVotes);
                    $getVoteStatus->fetch();
                    $getVoteStatus->close();
                    
                    
            ?>
                    <!-- click category button to view all posts within this category -->
                    <form method="GET" action="specificCategory.php">
                        <input type="submit" name="categoryButton" value="<?php echo $favCatArr[$incr] ?>" />
                    </form>
            <?php
                    //if you voted, can't vote again
                    if ($yourVotes >= 1) {
                        print("<form method='POST' action='mainPage.php'>");
                    } else {
                        print ("<form method='POST' action='processVotes.php'>");
                    }
                    
                    //print out post information
                    print("$favDescArr[$incr]<br>");
                    print("<a href='$favLinkArr[$incr]' title='link'>$favLinkArr[$incr]</a><br>");
                    ?>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                    <?php
                    print("<input type='submit' name='upVoteButton' value='+' />&nbsp; $favVoteArr[$incr]&nbsp;
                          <input type='submit' name='downVoteButton' value='-' /><br>");
            ?>
                    <input type="hidden" name="idNum" value="<?php echo $favArr[$incr] ?>" />
                    <input type="hidden" name="lastPage" value="profilePage.php" />
                    </form>
                    
                    
            <?php
                    //get username associated with specific ID
                    $getUsernames = $mysqli->prepare("SELECT username FROM users WHERE id=?");
                    if (!$getUsernames) {
                        printf("Query Prep Failed Usernames4: %s\n", $mysqli->error);
                        exit;
                    }
                    $getUsernames->bind_param('i', $favUserArr[$incr]);
                    $getUsernames->execute();
                    $getUsernames->bind_result($postUser);
                    $getUsernames->fetch();
                    $getUsernames->close();
                    
                    
                    //link to take you to user profile
                    print("submitted by: <a href='profilePage.php?value_user=$postUser'>$postUser</a><br>");
                    
            ?>
            
                <!-- Comments button for each post -->
                <form method="GET" action="commentPage.php">
                    <input type="hidden" name="idPost" value="<?php echo $favArr[$incr] ?>" />
                    <input type="submit" name="commentButton" value="Comments" />
                    <br><br>
                </form>
                

            <?php
                    
                    $incr++;
                }
                    }
            ?>
            
        </body>
    </html>