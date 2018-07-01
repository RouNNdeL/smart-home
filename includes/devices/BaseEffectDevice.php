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
 * Date: 2018-05-16
 * Time: 14:15
 */

require_once __DIR__ . "/../Utils.php";

abstract class BaseEffectDevice extends SimpleRgbDevice
{
    const ACTIONS_TOGGLE_EFFECT = "ACTIONS_TOGGLE_EFFECT";

    /** @var Effect[] */
    private $effects;

    /** @var bool */
    private $effects_enabled;

    /** @var int */
    private $max_color_count;

    public $current_profile;

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
     * @param array $effects
     * @param bool $effects_enabled
     * @param int $current_profile
     */
    public function __construct(string $device_id, string $device_name, array $synonyms, bool $home_actions,
                                bool $will_report_state, int $color = 0xffffff, int $brightness = 100,
                                bool $on = true, $effects = [], $effects_enabled = true, $current_profile = 0
    )
    {
        parent::__construct($device_id, $device_name, $synonyms, $home_actions, $will_report_state, $color, $brightness, $on);
        $this->effects = $effects;
        $this->effects_enabled = $effects_enabled;
        $this->current_profile = $current_profile;
        $this->loadEffects();
    }

    public function getTraits()
    {
        $array = parent::getTraits();
        array_push($array, self::DEVICE_TRAIT_TOGGLES);
        return $array;
    }

    public function getAttributes()
    {
        $name_values = [];
        foreach(Utils::AVAILABLE_LANGUAGES as $i => $lang)
        {
            $utils = new Utils($lang);
            $name_values[$i] = ["lang" => $lang, "name_synonym" => [
                $utils->_getString("actions_toggle_effect1"),
                $utils->_getString("actions_toggle_effect2")]
            ];
        }
        return ["availableToggles" => [
            ["name" => self::ACTIONS_TOGGLE_EFFECT, "name_values" => $name_values]
        ]];
    }

    /**
     * @param string $header_name
     * @param string $footer_html
     * @return string
     */
    public function toHtml($header_name = null, $footer_html = "")
    {
        if($header_name !== null)
            $name = $header_name;
        else
            $name = $this->device_name;
        $id = urlencode($this->device_id);
        $display_name = urlencode($this->device_name);
        $checked = $this->on ? "checked" : "";
        $color = "#" . str_pad(dechex($this->color), 6, '0', STR_PAD_LEFT);
        return <<<HTML
        <form>
            <div class="card-header">
                <div class="row">
                    <div class="col"><h6 class="align-middle mb-0">$name</h6></div>
                    <div class="col-auto float-right pl-0">
                        <input class="checkbox-switch change-listen" type="checkbox" name="state" $checked
                            data-size="small" data-label-width="10" id="state-$this->device_id">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <p class="mb-0">Brightness</p>
                        <div class="slider-container"> 
                            <input
                                class="slider change-listen"
                                type="text"
                                name="brightness"
                                id="brightness-$this->device_id"
                                value="$this->brightness">
                        </div>
                        <div class="color-container row mt-3">
                            <div class="col px-1">
                                <div class="color-picker-init" >
                                    <input id="color-$this->device_id" name="color" type="text change-listen" class="form-control color-input" value="$color"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col">
                        <a href="/effect/$display_name/$id"><small class="align-middle text-muted">Effect settings</small></a>$footer_html 
                    </div>
                </div>
            </div>
    </form>
HTML;
    }

    public function toAdvancedHtml(int $effect)
    {
        $device = $this->device_id;
        $profile_colors = Utils::getString("profile_colors");
        $profile_effect = Utils::getString("profile_effect");
        $profile_color_input = Utils::getString("profile_color_input");
        $profile_add_color = Utils::getString("profile_add_color");
        $color_limit = $this->max_color_count;
        $current_effect = $this->effects[$effect];

        $color_template = Effect::getColorTemplateLocalized();
        $colors_html_e = $current_effect->colorsHtml($color_limit);
        $colors_html = $colors_html_e === null ? "" :
            "<div class=\"row\">
                <div class=\"col pr-0\"><h3 class=\"header-colors\">$profile_colors</h3></div>
                <div class=\"col-auto pr-3\">
                    <button class=\"add-color-btn btn btn-primary btn-sm color-swatch$btn_class\" 
                            type=\"button\">$profile_add_color</button>
                </div>
            </div>
            <div class=\"swatch-container\" data-color-limit=\"$color_limit\">
                $colors_html_e
            </div><div class='color-swatch-template d-none'>$color_template</div> ";
        $effects_html = "";
        $html = "<form>";

        foreach($this->getAvailableEffects() as $id => $effect)
        {
            $string = Utils::getString("profile_effect_" . $effect);
            $effects_html .= "<option value=\"$id\" " . ($id == $current_effect->getEffectId() ? " selected" : "") . ">$string</option>";
        }

        $btn_class = sizeof($current_effect->getColors()) >= $color_limit ? " hidden-xs-up" : "";
        $html .= "<div class=\"main-container row m-2\">
        <div class=\"col-12 col-sm-6 col-lg-4 col-xl-3 mb-3 mb-lg-0\">
            <div class=\"form-group\">
                <h3>$profile_effect</h3>
                <select class=\"form-control effect-select\" name=\"effect\" id=\"effect-select-$device\">
                    $effects_html
                </select>
            </div>
            $colors_html
        </div>";
        $html .= $current_effect->timingArgHtml();
        $html .= "</form></div>";

        return $html;
    }

    public function addEffect($effect)
    {
        $this->effects[] = $effect;
        $this->toDatabase();
    }

    public abstract function getAvailableEffects();

    private function loadEffects()
    {
        $this->effects = Effect::forDevice($this->device_id);
    }

    public function toDatabase()
    {
        $conn = DbUtils::getConnection();
        $state = $this->on ? 1 : 0;
        $toggles = (($this->effects_enabled ? 1 : 0) << 0);
        $sql = "UPDATE devices_virtual SET 
                  color = ?,
                  brightness = ?, 
                  state = ?,
                  toggles = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiis", $this->color, $this->brightness, $state, $toggles, $this->device_id);
        $result = $stmt->execute();
        $stmt->close();

        foreach($this->effects as $effect)
        {
            $result = $result && $effect->toDatabase();
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function areEffectsEnabled(): bool
    {
        return $this->effects_enabled;
    }

    /**
     * @param bool $effects_enabled
     */
    public function setEffectsEnabled(bool $effects_enabled)
    {
        $this->effects_enabled = $effects_enabled;
    }

    /**
     * @param int $max_color_count
     */
    public function setMaxColorCount(int $max_color_count)
    {
        $this->max_color_count = $max_color_count;
    }

    /**
     * @return Effect[]
     */
    public function getEffects(): array
    {
        return $this->effects;
    }

    public function setEffect(int $index, Effect $effect)
    {
        $this->effects[$index] = $effect;
    }
}