<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Krzysztof "RouNdeL" Zdulski
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

$manager->loadUserDeviceManager([ShareManager::SCOPE_SIMPLE_CONTROL]);

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

if(isset($_GET["name"]) && $_GET["name"] === "false")
{
    header("Location: /effect/" . urlencode($device->getDeviceName()) . "/" . urlencode($device->getDeviceId()));
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . "/../../includes/head/HtmlHead.php";
$head = new HtmlHead("Smart Home - " . $device->getDeviceName());
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::DEVICE_ADVANCED));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::DEVICE_ADVANCED));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::MATERIAL_ICONS));
echo $head->toString();

?>
<body>
<?php
require_once __DIR__."/../../includes/navbar/Nav.php";

echo Nav::getDefault(Nav::PAGE_EFFECTS)->toString();
?>
<div class="container-fluid">
    <div class="row device-settings-content" data-device-id="<?php echo $device->getDeviceId() ?>">
        <div class="col mt-3">
            <div class="card">
                <div class="card-header">
                    <h2><?php echo $device->getDeviceName()?></h2>
                </div>
                <div class="card-body">
                    <?php
                    $effects = $device->getEffects();
                    ?>
                    <ul class="nav nav-pills" role="tablist">
                        <?php
                        foreach($effects as $i => $effect)
                        {
                            $active = $i ? "" : "active";
                            $name = htmlspecialchars($effect->getName());
                            $effect_id = "e-".$effect->getId();
                            echo <<<HTML
                        <li class="nav-item">
                            <a class="nav-link $active" id="$effect_id-tab" data-toggle="tab" href="#$effect_id" 
                            role="tab" aria-controls="$effect_id" aria-selected="false">$name</a>
                        </li>
HTML;
                        }
                        $add_effect_string = Utils::getString("effect_add_btn");
                        echo <<<HTML
                        <li class="nav-item px-1">
                            <button class="btn btn-outline-primary effect-add-btn" title="$add_effect_string"><i class="material-icons">add</i></button>
                        </li>
HTML;

                        ?>
                    </ul>
                    <div class="tab-content">
                        <?php
                        foreach($effects as $i => $effect)
                        {
                            $active = $i ? "" : "show active";
                            $e_id = $effect->getId();
                            $effect_id = "e-".$e_id;
                            $effectHtml = $device->effectHtml($e_id);
                            echo <<<HTML
                        <div class="tab-pane fade $active effect-parent" id="$effect_id" 
                         role="tabpanel" aria-labelledby="$effect_id-tab">
                            $effectHtml
                        </div>
HTML;
                        }
                        ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" role="button" type="submit" id="effect-save-btn">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="snackbar"></div>
</body>
</html>