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
 * Date: 2018-05-16
 * Time: 14:15
 */

require_once __DIR__ . "/../Utils.php";

abstract class BaseEffectDevice extends SimpleRgbDevice {
    const ACTIONS_TOGGLE_EFFECT = "effect";

    const TOGGLE_EFFECT_BIT = 0;

    /** @var Effect[] */
    private $effects;

    /** @var bool */
    private $effects_enabled;

    /** @var int */
    private $max_color_count;

    /** @var int */
    private $max_effect_count;

    /** @var int */
    private $max_active_effect_count;

    private $effect_indexes = [];

    /**
     * RgbEffectDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param array $synonyms
     * @param bool $home_actions
     * @param bool $will_report_state
     * @param int $color
     * @param int $brightness
     * @param bool $on
     * @param int $toggles
     */
    public function __construct(string $device_id, string $device_name, array $synonyms, bool $home_actions,
                                bool $will_report_state, int $color = 0xffffff, int $brightness = 100,
                                bool $on = true, int $toggles = 0,
                                $max_color_count = null, $max_effect_count = null, $max_active_effect_count = null
    ) {
        parent::__construct($device_id, $device_name, $synonyms, $home_actions, $will_report_state, $color, $brightness, $on);
        $this->effects_enabled = $toggles & (1 << BaseEffectDevice::TOGGLE_EFFECT_BIT);
        $this->max_color_count = $max_color_count;
        $this->max_effect_count = $max_effect_count;
        $this->max_active_effect_count = $max_active_effect_count;
        $this->loadEffects();
        $this->loadEffectIndexes();
    }

    public function handleSaveJson($json) {
        parent::handleSaveJson($json);
        $this->effects_enabled = $json["effects_enabled"];
    }

    public function handleAssistantAction($command) {
        if($command["command"] == VirtualDevice::DEVICE_COMMAND_SET_TOGGLES) {
            if(isset($command["params"]) && isset($command["params"]["updateToggleSettings"])) {
                $this->effects_enabled =
                    $command["params"]["updateToggleSettings"][BaseEffectDevice::ACTIONS_TOGGLE_EFFECT];
            }
        }
        parent::handleAssistantAction($command);
    }

    public function getStateJson(bool $online = false) {
        $json = parent::getStateJson($online);
        $json["currentToggleSettings"][BaseEffectDevice::ACTIONS_TOGGLE_EFFECT] = $this->effects_enabled;
        return $json;
    }


    public function getTraits() {
        $array = parent::getTraits();
        array_push($array, self::DEVICE_TRAIT_TOGGLES);
        return $array;
    }

    public function getAttributes() {
        $attributes = parent::getAttributes();
        $name_values = [];
        $name_values[] = ["lang" => "en", "name_synonym" =>
            ["effect", "effects", "light effect", "light effects", "led effects"]];
        $attributes[self::DEVICE_ATTRIBUTE_AVAILABLE_TOGGLES] = [
            ["name" => self::ACTIONS_TOGGLE_EFFECT, "name_values" => $name_values]
        ];
        return $attributes;
    }

    /**
     * @param string $header_name
     * @param string $footer_html
     * @return string
     */
    public function toHtml($header_name = null, $footer_html = "") {
        if($header_name !== null)
            $name = $header_name;
        else
            $name = $this->device_name;
        $id = urlencode($this->device_id);
        $display_name = urlencode($this->device_name);
        $checked = $this->on ? "checked" : "";
        $checked_effects = $this->effects_enabled ? "checked" : "";
        $color = "#" . str_pad(dechex($this->color), 6, '0', STR_PAD_LEFT);

        $center_row = strlen($footer_html) === 0 ? "justify-content-center" : "";
        $center_col = strlen($footer_html) === 0 ? "col-auto" : "col";
        return <<<HTML
        <form>
            <div class="card-header">
                <div class="row">
                    <div class="col text-center-vertical"><h6 class="mb-0">$name</h6></div>
                    <div class="col-auto float-right pl-0">
                        <input data-input-class="base-effect-state" class="checkbox-switch change-listen" type="checkbox" name="state" $checked
                            data-size="small" data-label-width="10" id="state-$this->device_id">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row $center_row">
                    <div class="$center_col">
                        <p class="mb-2">Brightness</p>
                        <div class="slider-container"> 
                            <input data-input-class="base-effect-brightness"
                                class="slider change-listen"
                                type="text"
                                name="brightness"
                                id="brightness-$this->device_id"
                                value="$this->brightness">
                        </div>
                        <div class="input-group mt-3">
                            <label for="effects-$this->device_id">Effects enabled
                            <div class="ml-3 d-inline">
                                <input data-input-class="base-effect-effects" class="checkbox-switch change-listen" type="checkbox" name="effects_enabled" $checked_effects
                                data-size="mini" data-label-width="10" id="effects-$this->device_id">
                            </label>
                            </div>
                        </div>
                        <div class="color-container row">
                            <div class="col">
                                <div data-input-class="base-effect-color" class="color-picker-init" >
                                    <input id="color-$this->device_id" name="color" type="text change-listen" class="form-control color-input" value="$color"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer py-2">
                <div class="row">
                    <div class="col text-center-vertical">
                        <a href="/effect/$display_name/$id" class="mb-1"><small class="align-middle text-muted">Effect settings</small></a> 
                    </div>
                    $footer_html
                </div>
            </div>
    </form>
HTML;
    }

    public function effectHtml(int $effect_id) {
        $device = $this->device_id;
        $profile_colors = Utils::getString("profile_colors");
        $profile_effect = Utils::getString("profile_effect");
        $profile_name = Utils::getString("effect_name");
        $profile_add_color = Utils::getString("profile_add_color");
        $max_colors = $this->max_color_count;
        $current_effect = $this->getEffectById($effect_id);
        $effect_name = htmlspecialchars($current_effect->getName());

        $max_colors = $current_effect->getMaxColors() === Effect::COLOR_COUNT_UNLIMITED ?
            $max_colors : min($max_colors, $current_effect->getMaxColors());
        $min_colors = $current_effect->getMinColors();

        $disabled = sizeof($current_effect->getColors()) >= $max_colors ? "disabled" : "";

        $color_template = Effect::getColorTemplateLocalized();
        $colors_html_e = $current_effect->colorsHtml($max_colors);
        $colors_html = $colors_html_e === null ? "" :
            "<div class=\"row\">
                <div class=\"col pr-0\"><h4 class=\"header-colors\">$profile_colors</h4></div>
                <div class=\"col-auto pr-3\">
                    <button class=\"add-color-btn btn btn-primary btn-sm color-swatch\" 
                            type=\"button\" $disabled>$profile_add_color</button>
                </div>
            </div>
            <div class=\"swatch-container\" data-color-limit=\"$max_colors\">
                $colors_html_e
            </div><div class='color-swatch-template d-none'>$color_template</div> ";
        $effects_html = "";
        $html = "<form data-effect-id=\"$effect_id\" data-max-colors=\"$max_colors\" data-min-colors=\"$min_colors\">";

        foreach($this->getAvailableEffects() as $id => $effect_id) {
            $string = Utils::getString("profile_effect_" . $effect_id);
            $effects_html .= "<option value=\"$id\" " . ($id == $current_effect->getEffectId() ? " selected" : "") . ">$string</option>";
        }

        $name_placeholder = Utils::getString("effect_default_name") . " $effect_id";
        $html .= "<div class=\"row mt-2\">
        <div class=\"col-24 col-sm-12 col-lg-8 col-xl-6 mb-3 mb-lg-0\">
            <div class=\"form-group\">
                <h4>$profile_name</h4>
                <input class='form-control effect-name-input' name='effect_name' value=\"$effect_name\" placeholder='$name_placeholder'>
            </div>
            <div class=\"form-group\">
                <h4>$profile_effect</h4>
                <select class=\"form-control effect-select\" name=\"effect\" id=\"effect-select-$device\">
                    $effects_html
                </select>
            </div>
            $colors_html
        </div>";
        $html .= $current_effect->timingArgHtml();
        $html .= "</div></form>";

        return $html;
    }

    public function updateEffect(Effect $effect) {
        foreach($this->effects as $i => $e) {
            if($e->getId() === $effect->getId()) {
                $this->effects[$i] = $effect;
                $effect->toDatabase();
                return $this->updateEffectIndexes($effect->getId());
            }
        }
        return $this->addEffect($effect);
    }

    /**
     * Attempts to find room for new effect_id, if indexes exceed max effect count
     * removes the least recently modified effect and inserts the new one in it's place
     *
     * @param int $effect_id
     */
    private function updateEffectIndexes(int $effect_id) {
        /* Effect already indexed */
        if(($key = array_search($effect_id, $this->effect_indexes)) !== false) {
            return $key;
        }

        if(sizeof($this->effect_indexes) < $this->max_active_effect_count) {
            $this->effect_indexes[] = $effect_id;
            return sizeof($this->effect_indexes) - 1;
        }

        $oldest = $this->getOldestEffectId();
        if(($key = array_search($oldest, $this->effect_indexes)) !== false) {
            $this->effect_indexes[$key] = $effect_id;
            return $key;
        }

        throw new UnexpectedValueException("$oldest key is not present in effect_indexes");
    }

    private function getOldestEffectId() {
        $old = time();
        $id = -1;
        foreach($this->effect_indexes as $effect_id) {
            $effect = $this->getEffectById($effect_id);
            $d = $effect->getLastModificationDate();
            if($d < $old) {
                $old = $d;
                $id = $effect_id;
            }
        }
        return $id;
    }

    public function getEffectIdByIndex(int $index) {
        return $this->effects[$index]->getId();
    }

    public function getEffectById(int $effect_id) {
        foreach($this->effects as $effect) {
            if($effect->getId() === $effect_id)
                return $effect;
        }
        return null;
    }

    public function addEffect(Effect $effect) {
        $this->effects[] = $effect;
        $effect->toDatabase();
        $this->updateEffectIndexes($effect->getId());
        return sizeof($this->effects) - 1;
    }

    public abstract function getAvailableEffects();

    public abstract function getDefaultEffect();

    private function loadEffects() {
        $this->effects = Effect::forDevice($this->device_id);
    }

    private function loadEffectIndexes() {
        $conn = DbUtils::getConnection();
        $sql = "SELECT effect_id, device_index FROM devices_effect_join WHERE device_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $this->device_id);
        $stmt->bind_result($effect_id, $index);
        $stmt->execute();
        while($stmt->fetch()) {
            if($index !== null && $index < $this->max_active_effect_count)
                $this->effect_indexes[$index] = $effect_id;
        }
        $stmt->close();
    }

    public function saveEffectIndexes() {
        $conn = DbUtils::getConnection();
        $sql = "UPDATE devices_effect_join SET device_index = NULL WHERE device_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $this->device_id);
        $stmt->execute();
        $stmt->close();

        $sql = /** @lang MySQL */
            "INSERT devices_effect_join SET effect_id = ?, device_index = ?, device_id = ? 
             ON DUPLICATE KEY UPDATE device_index = ?";
        $stmt = $conn->prepare($sql);

        $stmt->bind_param("iisi", $effect_id, $index, $this->device_id, $index);
        foreach($this->effect_indexes as $index => $effect_id) {
            $stmt->execute();
        }
        $stmt->close();
    }

    public function toDatabase() {
        $conn = DbUtils::getConnection();
        $state = $this->on ? 1 : 0;
        $toggles = (($this->effects_enabled ? 1 : 0) << BaseEffectDevice::TOGGLE_EFFECT_BIT);
        $sql = "UPDATE devices_virtual SET 
                  color = ?,
                  brightness = ?, 
                  state = ?,
                  toggles = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiis", $this->color, $this->brightness, $state, $toggles, $this->device_id);
        $stmt->execute();
        $changes = $stmt->affected_rows > 0 ? true : false;
        $stmt->close();

        foreach($this->effects as $effect) {
            $effect->toDatabase();
        }

        $this->saveEffectIndexes();
        return $changes;
    }

    /**
     * @return bool
     */
    public function areEffectsEnabled(): bool {
        return $this->effects_enabled;
    }

    /**
     * @param bool $effects_enabled
     */
    public function setEffectsEnabled(bool $effects_enabled) {
        $this->effects_enabled = $effects_enabled;
    }

    /**
     * @return Effect[]
     */
    public function getEffects(): array {
        return $this->effects;
    }

    public function setEffect(int $index, Effect $effect) {
        $this->effects[$index] = $effect;
    }
}