<?php

session_start();
require './functions.php';
require './settings.php';
$mysqliconnection = new mysqli(MYSQL_ADDRESS, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DB);
if (logged_in() === TRUE) {
    ActivityLog('Logged out', 1);
    session_destroy();
    header('Location: ../index.php');
} else {
    header('Location: ../index.php');
}
$mysqliconnection->close();
