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
 * Date: 2018-06-20
 * Time: 17:44
 */

require_once __DIR__ . "/SceneEntry.php";

class Scene {
    const ID_PREFIX = "scene_";

    /** @var int */
    private $id;

    /** @var */
    private $name;

    /** @var SceneEntry[] */
    private $entries;

    /**
     * Scene constructor.
     * @param $id
     * @param $name
     * @param SceneEntry[] $entries
     */
    private function __construct(int $id, string $name, array $entries) {
        $this->id = $id;
        $this->name = $name;
        $this->entries = $entries;
    }

    public static function fromId(int $scene_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT name FROM devices_effect_scenes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $scene_id);
        $stmt->bind_result($name);
        $stmt->execute();
        if($stmt->fetch()) {
            $stmt->close();
            return new Scene($scene_id, $name, SceneEntry::getForSceneId($scene_id));
        }
        $stmt->close();
        return null;
    }

    /**
     * @param $user_id
     * @return Scene[]
     */
    public static function allForUserId(int $user_id) {
        $entries = SceneEntry::getForUserId($user_id);

        $conn = DbUtils::getConnection();
        $sql = "SELECT id, name FROM devices_effect_scenes WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->bind_result($scene_id, $name);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            $entries_for_scene = isset($entries[$scene_id]) ? $entries[$scene_id] : [];
            $arr[] = new Scene($scene_id, $name, $entries_for_scene);
        }
        return $arr;
    }

    public function getSceneHtml(UserDeviceManager $manager) {
        $html = "<div class=\"list-group\">";
        foreach($this->entries as $entry) {
            $device_id = $entry->getDeviceId();
            $device = $manager->getVirtualDeviceById($device_id);
            if(!$device instanceof BaseEffectDevice)
                throw new UnexpectedValueException("SceneEntry for profile id: $this->id contains pointer to invalid device: $device_id");
            $effect_id = $entry->getEffectId();
            $effect = $device->getEffectById($effect_id);

            $effect_url = "/effect/$device_id#e-$effect_id";
            $device_name = $device->getDeviceName();
            $effect_name = htmlspecialchars($effect->getName());
            $title_delete = Utils::getString("scene_btn_hint_delete_entry");
            $title_jump = Utils::getString("scene_btn_hint_show_effect");

            $html .= <<<HTML
            <a href="$effect_url" class="list-group-item list-group-item-action flex-column align-items-start col-24 col-md-12 col-xl-8">
                <div class="row">
                    <div class="col profile-entry">
                        <h5 class="mb-1">$effect_name</h5>
                        <p class="mb-1">$device_name</p>
                    </div>
                    <div class="col float-right col-auto text-center-vertical pr-1">
                        <button class="btn effect-show-button btn-secondary" type="button" role="button" title="$title_jump"><span class="oi oi-action-redo"></span></button>
                    </div>
                    <div class="col float-right col-auto text-center-vertical pl-1">
                        <button class="btn btn-danger scene-entry-delete-btn" type="button" role="button" 
                title="$title_delete"><span class="oi oi-trash"></span></button>
                    </div>
                </div>
            </a>
HTML;
        }
        $html .= "</div>";
        return $html;
    }

    /** @return string */
    public function getPrefixedId() {
        return Scene::ID_PREFIX . $this->id;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function getSyncJson() {
        return [
            "id" => $this->getPrefixedId(),
            "name" => ["name" => $this->name],
            "type" => VirtualDevice::DEVICE_TYPE_ACTIONS_SCENE,
            "traits" => [VirtualDevice::DEVICE_TRAIT_SCENE],
            "willReportState" => false,
            "attributes" => [VirtualDevice::DEVICE_ATTRIBUTE_SCENE_REVERSIBLE => false]
        ];
    }
}