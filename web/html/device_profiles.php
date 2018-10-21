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
 * Date: 2018-08-02
 * Time: 12:15
 */

require_once __DIR__ . "/../../includes/effects/profiles/Profile.php";
require_once __DIR__ . "/../../includes/GlobalManager.php";

$manager = GlobalManager::all();

$profiles = Profile::allForUser($manager->getSessionManager()->getUserId());

?>

<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . "/../../includes/head/HtmlHead.php";
$head = new HtmlHead("Smart Home - Profiles");
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::DEVICE_PROFILES));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::DEVICE_PROFILES));
echo $head->toString();

?>
<body>
<?php
require_once __DIR__."/../../includes/navbar/Nav.php";

echo Nav::getDefault(Nav::PAGE_PROFILES)->toString();
?>
<div class="container-fluid">
    <div class="row device-settings-content">
        <div class="col mt-3">
            <div class="card">
                <div class="card-header">
                    <h2>Profiles</h2>
                </div>
                <div class="card-body">
                    <?php
                    ?>
                    <ul class="nav nav-pills" role="tablist">
                        <?php
                        foreach($profiles as $i => $profile)
                        {
                            $active = $i ? "" : "active";
                            $name = $profile->getName();
                            $profile_id = "p-".$profile->getId();
                            echo <<<HTML
                        <li class="nav-item">
                            <a class="nav-link $active" id="$profile_id-tab" data-toggle="tab" href="#$profile_id" 
                            role="tab" aria-controls="$profile_id" aria-selected="false">$name</a>
                        </li>
HTML;
                        }
                        ?>
                    </ul>
                    <div class="tab-content">
                        <h4 class="my-2">Device profile list</h4>
                        <?php
                        foreach($profiles as $i => $profile)
                        {
                            $active = $i ? "" : "show active";
                            $profile_id = "p-".$profile->getId();
                            $html = $profile->getProfileHtml();
                            echo <<<HTML
                        <div class="tab-pane fade $active effect-parent" id="$profile_id" 
                         role="tabpanel" aria-labelledby="$profile_id-tab">
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