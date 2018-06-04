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
 * Date: 5/31/2018
 * Time: 1:53 PM
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";

$manager = GlobalManager::withSessionManager(true);

if (!isset($_GET["device_id"])) {
    require __DIR__ . "/../error/404.php";
    http_response_code(404);
    exit(0);
}

$manager->loadUserDeviceManager();

$device = $manager->getUserDeviceManager()->getPhysicalDeviceById($_GET["device_id"]);
if($device === null)
{
    require __DIR__ . "/../error/404.php";
    http_response_code(404);
    exit(0);
}

if(isset($_GET["name"]) && $_GET["name"] === "false")
{
    header("Location: /device/" . urlencode($device->getDisplayName()) . "/" . urlencode($device->getId()));
}

?>

<!DOCTYPE html>
<html lang="en">
<?php
$title = "Smart Home";
$additional_css = ["main.css"];
require_once __DIR__ . "/../../web/html/html_head.php";

?>
<body>
<div class="container">


    <div class="card-body">
        <ul class="nav flex-column nav-pills">
            <?php
            echo $device->getDeviceNavbarHtml();
            ?>
        </ul>
    </div>
</div>
</body>
</html>