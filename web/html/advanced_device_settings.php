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
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::DEVICE_ADVANCED));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::DEVICE_SETTINGS));
echo $head->toString();

?>
<body>
<div class="container-fluid">
    <div class="row device-settings-content" data-device-id="<?php echo $device->getDeviceId() ?>">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h2>Mood Light</h2>
                </div>
                <div class="card-body">
                    <?php
                    $effects = $device->getEffects();
                    ?>
                    <ul class="nav nav-tabs" role="tablist">
                        <?php
                        foreach($effects as $i => $effect)
                        {
                            $active = $i ? "" : "active";
                            $name = $effect->getName();
                            $name_sanitized = Utils::sanitizeString($name);
                            echo <<<HTML
                        <li class="nav-item">
                            <a class="nav-link $active" id="$name_sanitized-tab" data-toggle="tab" href="#$name_sanitized" 
                            role="tab" aria-controls="$name_sanitized" aria-selected="false">$name</a>
                        </li>
HTML;
                        }
                        ?>
                    </ul>
                    <div class="tab-content">
                        <?php
                        foreach($effects as $i => $effect)
                        {
                            $active = $i ? "" : "show active";
                            $name_sanitized = Utils::sanitizeString($effect->getName());
                            $effect_id = $effect->getId();
                            $max_colors = $effect->getMaxColors() === Effect::COLOR_COUNT_UNLIMITED ?
                                $physical->getMaxColorCount() : min($physical->getMaxColorCount(), $effect->getMaxColors());
                            $min_colors = $effect->getMinColors();
                            $effectHtml = $device->toAdvancedHtml($i);
                            echo <<<HTML
                        <div class="tab-pane fade $active effect-parent" id="$name_sanitized" 
                         role="tabpanel" aria-labelledby="$name_sanitized-tab"
                         data-effect-id="$effect_id" data-max-colors="$max_colors" data-min-colors="$min_colors">
                            $effectHtml
                        </div>
HTML;
                        }
                        ?>
                    </div>
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