<?php

ob_start();
$execution_time = microtime(true);

require './Sources/functions.php';
require './Sources/settings.php';
session_start();
if (MYSQL_ENABLED) {
    $mysqliconnection = new mysqli(MYSQL_ADDRESS, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DB);

    if ($mysqliconnection->connect_errno) {
        die("Connection failed: " . $conn->connect_error);
    }
    if (!$mysqliconnection->set_charset("utf8")) {
        ActivityLog('Error loading character set utf8 Mysql', 3);
    }
}

$availableThemes = array(
    'Default'
);
$availableLanguages = array(
    'english'
);


$Theme_name = (isset($_COOKIE['THEME']) && in_array($_COOKIE['THEME'], $availableThemes)) ? $_COOKIE['THEME'] : SYSTEM_DEFAULT_THEME;

$Theme_lang = (isset($_COOKIE['LANG']) && in_array($_COOKIE['LANG'], $availableLanguages)) ? $_COOKIE['LANG'] : SYSTEM_DEFAULT_LANGUAGE;

/* Allowed pages
 * First one should be default page
 */
$availablePages = array(
    'Home' => 'index.php'
);
if (isset($_GET['page'])) {
    if (is_empty($_GET['page']) === FALSE && array_key_exists((strtolower($_GET['page'])), $availablePages) === FALSE) {
        header('Location: ' . SYSTEM_DOMAIN . 'error/404');
    }
    if (is_empty($_GET['page']) === TRUE or array_key_exists(strtolower($_GET['page']), $availablePages) === FALSE) {
        $page_name = 'index.php';
        $_GET['page'] = 'Home';
    } else {
        $page_name = $page_names[strtolower($_GET['page'])];
    }
} else {
    $page_name = 'index.php';
    $_GET['page'] = 'Home';
}
$Theme_path = './Themes/' . $Theme_name . '/' . $page_name;
require './Themes/Default/languages/default.' . $Theme_lang . '.php';
//initializing site

$base = '<base href="' . SYSTEM_DOMAIN . '" />';
if (file_exists('./Themes/' . $Theme_name . '/init.php')) {
    require './Themes/' . $Theme_name . '/init.php';
} else {
    die('Error, Failed to initialize theme');
}

if(MYSQL_ENABLED)
$mysqliconnection->close();
if (OUTPUT_USE_HTML_TIDY === TRUE) {
    $all_html = ob_get_contents();
    ob_end_clean();
    echo html_tidy($all_html);
}