<!DOCTYPE html>
    <html>
        <head>
            <title>LogoutPage</title>
        </head>
        
        <body>
            <?php
                session_start();
                unset($_SESSION['user']);
                session_destroy(); //log out
                header("Location: mainPage.php");
                exit;
            ?>
        </body>
    </html>