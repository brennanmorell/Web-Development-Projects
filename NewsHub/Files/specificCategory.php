<!DOCTYPE html>
    <html>
        <head>
            <title>Specific Category</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        </head>
        
        <body>
            <?php
                require 'database.php';
                session_start();
                if (isset($_GET['categoryButton'])) {
                    $category = $_GET['categoryButton'];
            ?>
                    <h2 align="center"><?php echo $category ?></h2>
            <?php
                    $getCPosts = $mysqli->prepare("SELECT id, description, link, user_id FROM posts WHERE category=?");
                    if (!$getCPosts) {
                        printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                        exit;
                    }
                    $getCPosts->bind_param('s', $category);
                    $getCPosts->execute();
                    $getCPosts->bind_result($postID, $postDesc, $postLink, $userID);
                    
                    $postIDArr = array();
                    $descArr = array();
                    $linkArr = array();
                    $userArr = array();
                    $incr = 0;
                    
                    while ($getCPosts->fetch()) {
                        $postIDArr[$incr] = $postID;
                        $descArr[$incr] = $postDesc;
                        $linkArr[$incr] = $postLink;
                        $IDArr[$incr] = $userID;
                        $incr++;
                    }
                    $getCPosts->close();
                    
                    $incr = 0;
                    
                    while ($incr < count($descArr)) {
                        
                        printf("%s <br>", $descArr[$incr]);
                        print("<a href='$linkArr[$incr]' title='link'>$linkArr[$incr]</a><br>");
                        
                        
                        $getUsernames = $mysqli->prepare("SELECT username FROM users WHERE id=$IDArr[$incr]");
                        if (!$getUsernames) {
                            printf("Query Prep Failed Usernames: %s\n", $mysqli->error);
                            exit;
                        }
                        $getUsernames->execute();
                        $getUsernames->bind_result($postUser);
                        $getUsernames->fetch();
                        $getUsernames->close();
                        
                        
                        print("submitted by: <a href='profilePage.php?value_user=$postUser'>$postUser</a><br>");
                        if ($_SESSION['user'] == $postUser) {
                ?>
                            <!-- user submitted this post, can edit or delete this post -->
                            <form method="POST" action="editPost.php">
                                <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                                <input type="submit" name="editButton" class='btn btn-default btn-xs' value="Edit" />
                            </form>
                            <form method="POST" action="deletePost.php">
                                <input type="hidden" name="idNum" value="<?php echo $postIDArr[$incr] ?>" />
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                                <input type="submit" name="deleteButton" class='btn btn-default btn-xs' value="Delete" />
                            </form>
                <?php
                        }
                ?>
                
                    <!-- Comments button for each post -->
                    <form method="GET" action="commentPage.php">
                        <input type="hidden" name="idPost" value="<?php echo $postIDArr[$incr] ?>" />
                        <input type="submit" name="commentButton" class='btn btn-default btn-xs' value="Comments" />
                        <br><br>
                    </form>
                    
            <?php
                    $incr++;
                    }
                }
            ?>
            
            <form action="mainPage.php">
                <input type="submit" name='backToMain' class='btn btn-default btn-s' value="Back" />
            </form>
            
            
        </body>
    </html>