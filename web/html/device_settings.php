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

if(!isset($_GET["device_id"]))
{
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
$virtualDevices = $device->getVirtualDevices();
?>

<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . "/../../includes/head/HtmlHead.php";
$head = new HtmlHead("Smart Home - " . $device->getDisplayName());
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::DEVICE_SETTINGS));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::DEVICE_SETTINGS));
echo $head->toString();

?>
<body>
<div class="container-fluid">
    <div class="row device-settings-content">
        <div class="col-sm-12">
                <?php
                $reboot_string = Utils::getString("device_reboot");
                if(sizeof($virtualDevices) > 1)
                {
                    $virtual_html = "";
                    foreach($virtualDevices as $i => $virtualDevice)
                    {
                        $html = $virtualDevice->toHtml();
                        $id = $virtualDevice->getDeviceId();
                        $virtual_html .= <<<HTML
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 px-1 py-1">
                            <div class="card device-parent" data-device-id="$id">
                                $html
                            </div> 
                        </div>
HTML;

                    }

                    $device_name = $device->getNameWithState();
                    echo <<<HTML
                    <div class="card">
                        <div class="card-header">
                            <h4>$device_name</h4>
                        </div>
                        <div class="card-body px-3 py-2">
                            <div class="row px-2 py-0">
                                $virtual_html
                            </div>
                        </div>
                        <div class="card-footer">
                        <button id="device-settings-submit"
                            class="btn btn-danger">$reboot_string</button>
                        </div>
                    </div>
HTML;

                }
                else
                {
                    $footer = <<<HTML
                    <button id="device-settings-submit" class="btn btn-sm btn-danger float-right">$reboot_string</button>
HTML;

                    $virtual_html = $virtualDevices[0]->toHtml($device->getNameWithState(), $footer);
                    $id = $virtualDevices[0]->getDeviceId();
                    echo <<<HTML
                    <div class="card device-parent" data-device-id="$id">
                            $virtual_html
                    </div> 
HTML;

                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>