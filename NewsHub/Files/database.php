<?php
    $mysqli = new mysqli('localhost', 'newssite', 'newssql', 'Module3'); //database to be used
     
    if($mysqli->connect_errno) {
        printf("Connection Failed: %s\n", $mysqli->connect_error);
        exit;
    }
?>