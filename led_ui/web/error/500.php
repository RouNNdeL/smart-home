<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:32
 */
require_once(__DIR__ . "/../includes/Utils.php");
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;
require_once(__DIR__."/../includes/html_head.php");
$msg = Utils::getInstance()->getString("error_msg_500");
echo <<<TAG
<body>
<h1>500 Error</h1>
<p>$msg</p>
</body>
TAG;
?>
