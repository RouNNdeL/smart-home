<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 16:43
 */
$add = "";
if(isset($additional_css))
{
    foreach ($additional_css as $css)
    {
        $add.="<link rel=\"stylesheet\" href=\"/smart/web/css/$css\"/>";
    }
}
if(isset($additional_js))
{
    foreach ($additional_js as $js)
    {
        $add.="<script src=\"/smart/web/js/$js\"/></script>";
    }
}
if(!isset($title))
{
    $title = "Smart Home";
}
echo <<<TAG
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>$title</title>
    <script src="/web/js/jquery-3.2.1.min.js"></script>
    <script src="/tether-1.3.3/dist/js/tether.min.js"></script>
    <script src="/bootstrap-4/js/bootstrap.min.js"></script>
    <script src="/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
    <script src="/jquery_ui/jquery-ui.min.js"></script>
    <script src="/web/js/bootstrap-slider.min.js"></script>
    <link rel="stylesheet" href="/bootstrap-4/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css"/>
    <link rel="stylesheet" href="/iconic/font/css/open-iconic-bootstrap.min.css"/>
    <link rel="stylesheet" href="/tether-1.3.3/dist/css/tether.min.css"/>
    <link rel="stylesheet" href="/jquery_ui/jquery-ui.min.css"/>
    <link rel="stylesheet" href="/web/css/main.css"/>
    <link rel="stylesheet" href="/web/css/bootstrap-slider.min.css"/>
    $add
</head>
TAG;
