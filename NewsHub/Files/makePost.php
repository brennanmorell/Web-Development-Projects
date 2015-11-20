<!DOCTYPE html>
    <html>
        <head>
            <title>Make Post</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        </head>
        
        <body>
            <?php
                session_start();
                if($_SESSION['token'] !== $_POST['token']){
                    die("Request forgery detected");
                }
                $username = $_SESSION['user'];
            ?>
            <!-- display username -->
            <div align="right">
                <?php print("<a href='profilePage.php?value_user=$username'>$username</a><br>"); ?>
            </div>
            
            <!-- Type description and link and pick category to submit a post-->
            <div align="center">
                <form method='POST' action='processBlog.php'>
                    <p>
                        <?php
                            if(isset($_SESSION['descErr'])) {
                                print($_SESSION['descErr']);
                                unset($_SESSION['descErr']);
                            }
                        ?>
                        <textarea id='description' name='description' rows='5' cols='50' placeholder="Post description"></textarea>
                    </p>
                    <label for="link"></label>
                    <input id='link' placeholder="Link" type='url' name='link' />
                    <br>
                    <br>
                    <input type="radio" name="category" value="news" />News
                    <input type="radio" name="category" value="sports" />Sports
                    <input type="radio" name="category" value="photos" />Photos
                    <input type="radio" name="category" value="videos" />Videos
                    <input type="radio" name="category" value="other" />Other (type)
                    <textarea id="otherText" name="otherText" rows="1" cols="10" placeholder="other"></textarea>
                    &nbsp; &nbsp;
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                    <input type='submit' name='postArticle' class='btn btn-default btn-s' value='Share' />
                </form>
            </div>
            
            <form action="mainPage.php">
                <input type="submit" name='backToMain' value="Back" />
            </form>
        </body>
    </html>