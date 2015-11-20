<!DOCTYPE html>
    <html>
        <head>
            <title>Edit Comment</title>
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
        $idComment = $_POST['commentNum'];
        $_SESSION['idComment'] = $idComment;
        
        //get comment to be edited
        $editStmt = $mysqli->prepare("SELECT text FROM comments WHERE id=?");
        $editStmt->bind_param('i', $idComment);
        if (!$editStmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $editStmt->execute();
        $editStmt->bind_result($toEditComment); //save current comment into var
        $editStmt->fetch();
        $editStmt->close();
?>
        
            <!-- display username -->
            <div align="right">
                <?php //echo $username 
				print("submitted by: <a href='profilePage.php?value_user=$username'>$username</a><br>");
				?>
            </div>
            
            <!-- Type comment to submit for edit-->
            <div align="center">
                <form method="POST" action="submitEditComment.php">
                    <p>
                        <textarea id='comment' name='comment' rows='5' cols='50'><?php echo $toEditComment ?></textarea>
                    </p>
                    &nbsp; &nbsp;
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
                    <input type='submit' name='editCommentButton' class='btn btn-default btn-xs' value='Submit' />
                </form>
            </div>
<?php } ?>
        </body>
    </html>