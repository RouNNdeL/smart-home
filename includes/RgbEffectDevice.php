<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-16
 * Time: 14:15
 */

abstract class RgbEffectDevice extends SimpleRgbDevice
{
    const ACTIONS_TOGGLE_EFFECT = "ACTIONS_TOGGLE_EFFECT";

    /** @var Effect[] */
    private $effects;

    /** @var bool */
    private $effects_enabled;

    public $current_profile;

    /**
     * RgbEffectDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param int $color
     * @param int $brightness
     * @param bool $on
     * @param array $effects
     * @param bool $effects_enabled
     * @param int $current_profile
     */
    public function __construct(string $device_id, string $device_name, int $color = 0xffffff, int $brightness = 100,
                                bool $on = true, $effects = [], $effects_enabled = true, $current_profile = 0)
    {
        parent::__construct($device_id, $device_name, $color, $brightness, $on);
        $this->effects = $effects;
        $this->effects_enabled = $effects_enabled;
        $this->current_profile = $current_profile;

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

    public function toHTML()
    {
        $device = $this->device_id;
        $html = "<form id=\"device-form-$device\">";
        $profile_colors = Utils::getString("profile_colors");
        $profile_effect = Utils::getString("profile_effect");
        $profile_color_input = Utils::getString("profile_color_input");
        $profile_add_color = Utils::getString("profile_add_color");
        $color_limit = $this->colorLimit();

        $colors_html = "";
        $effects_html = "";

        foreach($this->getAvailableEffects() as $id => $effect)
        {
            $string = Utils::getString("profile_" . $effect);
            $effects_html .= "<option value=\"$id\"" . ($id == $this->effects[$this->current_profile] ? " selected" : "") . ">$string</option>";
        }

        $btn_class = sizeof($this->effects[$this->current_profile]->getColors()) >= $color_limit ? " hidden-xs-up" : "";
        $html .= "<div class=\"main-container row m-2\">
        <div class=\"col-12 col-sm-6 col-lg-4 col-xl-3 mb-3 mb-lg-0\">
        <div class=\"form-group\">
            <h3>$profile_effect</h3>
            <select class=\"form-control effect-select\" name=\"effect\" id=\"effect-select-$device\">
                $effects_html
            </select>
        </div>
        <div class=\"row\">
            <div class=\"col pr-0\"><h3 class=\"header-colors\">$profile_colors</h3></div>
            <div class=\"col-auto pr-3\">
                <button class=\"add-color-btn btn btn-primary btn-sm color-swatch$btn_class\" 
                        type=\"button\">$profile_add_color</button>
            </div>
        </div>
        <div class=\"swatches-container\" data-color-limit=\"$color_limit\">
            $colors_html
        </div>

    </div>";
        $html .= $this->effects[$this->current_profile]->timingArgHtml();
        $html .= "</form></div>";

        return $html;
    }

    public function addEffect($effect)
    {

    }

    public abstract function getAvailableEffects();

    public abstract function colorLimit();
}