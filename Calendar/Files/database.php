<?php
    $mysqli = new mysqli('localhost', 'calendarUser', 'calendarpw', 'module5'); //database to be used
     
    if($mysqli->connect_errno) {
        printf("Connection Failed: %s\n", $mysqli->connect_error);
        exit;
    }
?>