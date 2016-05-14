<?php

require './Themes/' . $Theme_name . '/languages/index.' . $Theme_lang . '.php';

class Page extends MainPageObject {

    public $additionalHeadTags = '<link rel="stylesheet" type="text/css" href="Themes/Default/css/index.css" />';

    function DisplayHTMLBody() {
        echo '<h1>Successful installation!</h1><p>Go through Sources/settings.php if you haven\'t yet.</p>';
    }

}
