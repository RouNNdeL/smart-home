<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-03-23
 * Time: 15:41
 */

require_once(__DIR__ . "/../includes/Utils.php");
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;
?>
<?php
$additional_js = ["support.js", "debug.js"];
require_once(__DIR__ . "/../includes/html_head.php");
?>
<body>

<?php require_once(__DIR__ . "/../../network/tcp.php"); ?>
<?php require_once(__DIR__ . "/../includes/Data.php"); ?>

<div class="container">
    <div class="row">
        <div class="col"><h3 class="text-center"><?php echo Utils::getString("debug_title"); ?></h3></div>
    </div>
    <div class="row justify-content-center">
        <div class="col col-auto">
            <p class="monospace" id="debug-data"><?php echo Data::getInstance()->formatDebug(null)?></p>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col col-auto">
            <button class="btn debug-button" data-action="increment" data-value="-64">-64</button>
            <button class="btn debug-button" data-action="increment" data-value="-4">-4</button>
            <button class="btn debug-button" data-action="increment" data-value="-1">-1</button>
            <button class="btn debug-button" data-action="pause" data-value="1"><span id="debug-pause-icon" class="oi oi-media-pause"></span></button>
            <button class="btn debug-button" data-action="increment" data-value="1">+1</button>
            <button class="btn debug-button" data-action="increment" data-value="4">+4</button>
            <button class="btn debug-button" data-action="increment" data-value="64">+64</button>
        </div>
    </div>
</div>
</body>
</html>
