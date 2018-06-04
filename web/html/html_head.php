<?php
/**
 * MIT License
 *
 * Copyright (c) 2018 Krzysztof "RouNdeL" Zdulski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 16:43
 */

//TODO: Make this a separate class

$add = "";
if(isset($additional_css))
{
    foreach ($additional_css as $css)
    {
        $add.="<link rel=\"stylesheet\" href=\"/web/css/$css\"/>";
    }
}
if(isset($additional_js))
{
    foreach ($additional_js as $js)
    {
        $add.="<script src=\"/web/js/$js\"/></script>";
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
    <script src="/jquery/jquery.min.js"></script>
    <script src="/tether/dist/js/tether.min.js"></script>
    <script src="/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
    <script src="/jqueryui/jquery-ui.min.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <link rel="stylesheet" href="/bootstrap/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css"/>
    <link rel="stylesheet" href="/iconic/font/css/open-iconic-bootstrap.min.css"/>
    <link rel="stylesheet" href="/tether/dist/css/tether.min.css"/>
    $add
</head>
TAG;
