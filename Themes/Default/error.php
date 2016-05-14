<?php

require './Themes/Default/languages/error.' . $Theme_lang . '.php';

class Page extends MainPageObject {

    public $additionalHeadTags = '<meta name="robots" content="noindex, nofollow" /><link rel="stylesheet" type="text/css" href="Themes/Default/css/error.css" />';

    public function Init() {
        global $txt, $message, $errortitle, $codes, $status;
        $status = explode('/', $_GET['p'])[0];
        $codes = array(
            400 => array('400 Bad Request', 'The server cannot or will not process the request due to something that is perceived to be a client error.'),
            401 => array('401 Login Error', 'It appears that the password and/or user-name you entered was incorrect.'),
            403 => array('403 Forbidden', 'Sorry, employees and staff only.'),
            404 => array('404 Not Found', 'The requested resource could not be found but may be avaible in the future.'),
            405 => array('405 Method Not Allowed', 'The method specified in the Request-Line is not allowed for the specified resource.'),
            408 => array('408 Request Timeout', 'Your browser failed to send a request in the time allowed by the server.'),
            414 => array('414 URL To Long', 'The URL you entered is longer than the maximum length.'),
            500 => array('500 Internal Server Error', 'The request was unsuccessful due to an unexpected condition encountered by the server.'),
            502 => array('502 Bad Gateway', 'The server received an invalid response from the upstream server while trying to fulfill the request.'),
            504 => array('504 Gateway Timeout', 'The upstream server failed to send a request in the time allowed by the server.')
        );

        $errortitle = $codes[$status][0];
        $message = $codes[$status][1];
        $txt['index_site_title'] = $errortitle;
        if ($errortitle == false) {
            $errortitle = "Unknown Error";
            $message = "An unknown error has occurred.";
        }
    }

    function DisplayHTMLBody() {
        global $txt, $message, $errortitle, $codes, $status;
        echo '<div><h1>' . $errortitle . '</h1><p>' . $message . '</p>';
        echo '</div>';
    }

}
