<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 20:16
 */
require_once(__DIR__ . "/../includes/Utils.php");
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;
require_once(__DIR__."/../includes/html_head.php");
$msg = Utils::getInstance()->getString("error_msg_404");
$msg = str_replace("\$url", "<b>$_SERVER[REQUEST_URI]</b>", $msg);
echo <<<TAG
<body>
<h1>404 Error</h1>
<p>$msg</p>
</body>
TAG;
?>
