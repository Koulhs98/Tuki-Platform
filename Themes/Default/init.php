<?php

class MainPageObject {

    public $meta_description = "Default meta description";
    public $author = "Sivusoft.com";
    public $additionalHeadTags = NULL;

    public function Init() {
        
    }

    public function DisplayHTMLTop() {
        echo '<!DOCTYPE html>
<html>';
    }

    public function DisplayHTMLHead($additiontags) {
        global $txt;
        echo '
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8" />
    ' . $this->additionalHeadTags . '
    <meta name="author" content="' . $this->author . '" />
    <meta name="description" content="' . $this->meta_description . '" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . $txt['SiteTitle'] . '</title>
</head>';
    }

    public function DisplayHTMLFirstBody() {
        echo '<body>';
    }

    public function DisplayHTMLBody() {
        echo '<h1>Default body</h1>';
    }

    public function DisplayHTMLLastBody() {
        echo '</body>';
    }

    public function DisplayHTMLLastHTML() {
        echo '</html>';
    }
}

require $Theme_path;
if (class_exists("Page")) {
    $page = new Page();
    if (method_exists($page, "customAssembly")) {
        $page->customAssembly();
    } else {
        $page->Init();
        $page->DisplayHTMLTop();
        $page->DisplayHTMLHead($page->additionalHeadTags);
        $page->DisplayHTMLFirstBody();
        $page->DisplayHTMLBody();
        $page->DisplayHTMLLastBody();
        $page->DisplayHTMLLastHTML();
    }
}