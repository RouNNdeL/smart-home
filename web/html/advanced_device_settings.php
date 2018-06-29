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
 * Date: 2018-06-21
 * Time: 08:54
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";

$manager = GlobalManager::withSessionManager(true);

if(!isset($_GET["device_id"]))
{
    require __DIR__ . "/../error/404.php";
    http_response_code(404);
    exit(0);
}

$manager->loadUserDeviceManager();

$physical = $manager->getUserDeviceManager()->getPhysicalDeviceByVirtualId($_GET["device_id"]);
if($physical === null || !$physical instanceof RgbEffectDevice)
{
    require __DIR__ . "/../error/404.php";
    http_response_code(404);
    exit(0);
}
$device = $physical->getVirtualDeviceById($_GET["device_id"]);
if($device === null || !$device instanceof BaseEffectDevice)
{
    require __DIR__ . "/../error/404.php";
    http_response_code(404);
    exit(0);
}
$device->setMaxColorCount($physical->getMaxColorCount());
if(isset($_GET["name"]) && $_GET["name"] === "false")
{
    header("Location: /device/" . urlencode($device->getDeviceName()) . "/" . urlencode($device->getDeviceId()));
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . "/../../includes/head/HtmlHead.php";
$head = new HtmlHead("Smart Home - " . $device->getDeviceName());
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::DEVICE_SETTINGS));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::DEVICE_SETTINGS));
echo $head->toString();

?>
<body>
<div class="container-fluid">
    <div class="row device-settings-content">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h2>Mood Light</h2>
                </div>
                <div class="card-body">
                    <?php echo $device->toAdvancedHtml(0)?>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>