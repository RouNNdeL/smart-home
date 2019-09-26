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

use App\Database\ShareManager;
use App\Effects\Scenes\Scene;
use App\GlobalManager;
use App\Head\HtmlHead;
use App\Head\JavaScriptEntry;
use App\Head\StyleSheetEntry;
use App\Navbar\Nav;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-08-02
 * Time: 12:15
 */

require_once __DIR__ . "/../../vendor/autoload.php";

$manager = GlobalManager::all([ShareManager::SCOPE_VIEW_EFFECTS]);

?>

<!DOCTYPE html>
<html lang="en">
<?php

$head = new HtmlHead("Smart Home - Profiles");
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::DEVICE_SCENES));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::DEVICE_SCENES));
echo $head->toString();

?>
<body>
<?php

$scenes = Scene::allForUserId($manager->getSessionManager()->getUserId());

echo Nav::getDefault(Nav::PAGE_SCENES)->toString();
?>
<div class="container-fluid">
    <div class="row device-settings-content">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h2>Scenes</h2>
                </div>
                <div class="card-body">
                    <?php
                    ?>
                    <ul class="nav nav-pills" role="tablist">
                        <?php
                        foreach($scenes as $i => $scene)
                        {
                            $active = $i ? "" : "active";
                            $name = $scene->getName();
                            $scene_id = "p-".$scene->getId();
                            echo <<<HTML
                        <li class="nav-item">
                            <a class="nav-link $active" id="$scene_id-tab" data-toggle="tab" href="#$scene_id" 
                            role="tab" aria-controls="$scene_id" aria-selected="false">$name</a>
                        </li>
HTML;
                        }
                        echo <<<HTML
                        <li class="nav-item px-1">
                            <button class="btn btn-outline-primary scene-add-btn">New scene</button>
                        </li>
HTML;

                        ?>
                    </ul>
                    <div class="tab-content pt-3">
                        <?php
                        foreach($scenes as $i => $scene)
                        {
                            $active = $i ? "" : "show active";
                            $scene_id = "p-".$scene->getId();
                            $html = $scene->getSceneHtml($manager->getUserDeviceManager());
                            echo <<<HTML
                        <div class="tab-pane fade $active effect-parent" id="$scene_id" 
                         role="tabpanel" aria-labelledby="$scene_id-tab">
                            $html
                        </div>
HTML;
                        }
                        ?>
                        <div class="row mt-2">
                            <div class="col">
                                <button class="btn btn-outline-primary">Add device</button>
                            </div>
                        </div>
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