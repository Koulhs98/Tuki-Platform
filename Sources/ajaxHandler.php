<?php

require './functions.php';
require './settings.php';

$Theme_name = (isset($_COOKIE['THEME']) && in_array($_COOKIE['THEME'], $availableThemes)) ? $_COOKIE['THEME'] : SYSTEM_DEFAULT_THEME;

$Theme_lang = (isset($_COOKIE['LANG']) && in_array($_COOKIE['LANG'], $availableLanguages)) ? $_COOKIE['LANG'] : SYSTEM_DEFAULT_LANGUAGE;
if (MYSQL_ENABLED) {
    $mysqliconnection = new mysqli(MYSQL_ADDRESS, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DB);
    if ($mysqliconnection->connect_errno) {
        echo 'Connection to database failed';
        exit();
    }
    if (!$mysqliconnection->set_charset("utf8")) {
        LogAction('Error loading character set utf8 Mysql', 3);
    }
}

// Handle things after this
// p1 stands for function
$p = $_POST['p'];

$allowed_functions = array(
    "AjaxRequest"
);

if (in_array($p, $allowed_functions)) {
    switch ($p) {
        case 'AjaxRequest':
            echo 'Return from ajaxHandler.php';
            break;
        default :
            die("function not defined");
    }
} else {
    echo 'Unknown function';
}


if (MYSQL_ENABLED)
$mysqliconnection->close();
