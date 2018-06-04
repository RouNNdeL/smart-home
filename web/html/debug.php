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
require_once(__DIR__ . "/../../web/includes/html_head.php");
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
