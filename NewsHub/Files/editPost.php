<!DOCTYPE html>
    <html>
        <head>
            <title>Edit Post</title>
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        </head>
        
        <body>
<?php
    require 'database.php';
    session_start();
    if($_SESSION['token'] !== $_POST['token']){
    	die("Request forgery detected");
    }
    if (isset($_POST['editButton'])) { //edit button set, do edit stuff
        $username = $_SESSION['user'];
        $idPost = $_POST['idNum'];
        $_SESSION['idPost'] = $idPost;
        
        //get description and link of post to be edited
        $editStmt = $mysqli->prepare("SELECT description, link FROM posts WHERE id=?");
        $editStmt->bind_param('i', $idPost);
        if (!$editStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $editStmt->execute();
        $editStmt->bind_result($toEditDesc, $toEditLink); //save current description and link into vars
        $editStmt->fetch();
        $editStmt->close();
?>
        
            <!-- display username -->
            <div align="right">
                <?php echo $username ?>
            </div>
            
            <!-- Type description and link to submit a post-->
            <div align="center">
                <form method="POST" action="submitEdit.php">
                    <p>
                        <?php
                            if(isset($_SESSION['descErr'])) {
                                print($_SESSION['descErr']);
                                unset($_SESSION['descErr']);
                            }
                        ?>
                        <textarea id='description' name='description' rows='5' cols='50'><?php echo $toEditDesc ?></textarea>
                    </p>
                    <label for="link"></label>
                    <input id='link' type='url' name='link' value="<?php echo $toEditLink ?>" />
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
                    <input type='submit' name='editPostButton' class='btn btn-default btn-s' value='Submit' />
                </form>
            </div>
<?php } ?>

            <form action="mainPage.php">
                <input type="submit" name='backToMain' class='btn btn-default btn-s' value="Back" />
            </form>
            
        </body>
    </html>