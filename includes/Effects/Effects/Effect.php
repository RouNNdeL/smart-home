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
 * Time: 13:43
 */

namespace App\Effects\Effects;

use App\Database\DbUtils;
use App\Effects\Arguments\Argument;
use App\Utils;
use InvalidArgumentException;
use mysqli_sql_exception;
use mysqli_stmt;
use UnexpectedValueException;

abstract class Effect {
    const AVR_EFFECT_BREATHE = 0x00;
    const AVR_EFFECT_FADE = 0x01;
    const AVR_EFFECT_FILLING_FADE = 0x02;
    const AVR_EFFECT_RAINBOW = 0x03;
    const AVR_EFFECT_FILL = 0x04;
    const AVR_EFFECT_ROTATING = 0x05;
    const AVR_EFFECT_PIECES = 0x0C;
    const AVR_EFFECT_PARTICLES = 0x06;
    const AVR_EFFECT_SPECTRUM = 0x07;

    const ARG_COLOR_CYCLES = "color_cycles";

    const EFFECT_OFF = 0;
    const EFFECT_STATIC = 1;
    const EFFECT_BREATHING = 2;
    const EFFECT_BLINKING = 3;
    const EFFECT_FADING = 4;
    const EFFECT_SIMPLE_RAINBOW = 5;
    const EFFECT_FILL = 6;
    const EFFECT_MARQUEE = 7;
    const EFFECT_ROTATING = 8;
    const EFFECT_SWEEP = 9;
    const EFFECT_ANDROID_PB = 10;
    const EFFECT_DOUBLE_FILL = 12;
    const EFFECT_HIGHS = 13;
    const EFFECT_SOURCES = 14;
    const EFFECT_PIECES = 15;
    const EFFECT_ROTATING_RAINBOW = 16;
    const EFFECT_FILLING_FADE = 17;
    const EFFECT_SPECTRUM = 18;
    const EFFECT_TWO_HALVES = 19;
    const EFFECT_TWO_HALVES_FADE = 20;
    const EFFECT_PARTICLES = 21;

    const DIRECTION_NORMAL = 1;
    const DIRECTION_REVERSE = 0;

    const TIME_OFF = 0;
    const TIME_FADEIN = 1;
    const TIME_ON = 2;
    const TIME_FADEOUT = 3;
    const TIME_ROTATION = 4;
    const TIME_DELAY = 5;

    const COLOR_COUNT_UNLIMITED = -1;

    const /** @noinspection CssInvalidPropertyValue */
        /** @noinspection HtmlUnknownAttribute */
        COLOR_TEMPLATE =
        "<div class=\"color-container row mb-1\">
            <div class=\"col-auto ml-3 pl-0 pr-1\">
                <button class=\"btn btn-danger color-delete-btn\" type=\"button\" role=\"button\" 
                title=\"\$title_delete\"><span class=\"oi oi-trash\" \$disabled></span></button>
            </div>
            <div class=\"col-auto pl-0 pr-1 d-none d-xs-block\">
                <button class=\"btn color-jump-btn\" type=\"button\" role=\"button\" title=\"\$title_jump\"><span class=\"oi oi-action-redo\"></span></button>
            </div>
            <div class=\"col pl-0\">
                <div class=\"input-group colorpicker-component\">
                    <input type=\"text\" class=\"form-control color-input\" value=\"\$color\" autocomplete=\"off\" 
                    aria-autocomplete=\"none\" spellcheck=\"false\"/>
                    <div class='input-group-append'><span class=\"input-group-text color-swatch-handle colorpicker-input-addon\"><i></i></span></div>
                </div>
            </div>
        </div>";

    const INPUT_TEMPLATE_ARGUMENTS = "<div class=\"col-auto form-group px-1 mb-1\"><label class=\"mb-0\">\$label</label>
                            <input class=\"form-control input-args\" type=\"text\" name=\"\$name\" 
                                    placeholder=\"\$placeholder\" value=\"\$value\" data-previous-value='\$value'></div>";
    const INPUT_TEMPLATE_TIMES = "<div class=\"col-auto form-group px-1 mb-1\"><label class=\"mb-0\">\$label</label>
                            <input class=\"form-control input-times\" type=\"text\" name=\"\$name\" placeholder=\"\$placeholder\"
                             value=\"\$value\" data-previous-value='\$value'></div>";

    const HIDDEN_TEMPLATE = "<input type=\"hidden\" name=\"\$name\" value=\"\$value\">";

    const TIMING_MODE_RAW = 0;
    const TIMING_MODE_SECONDS = 1;
    const TIMING_MODE_JSON = 2;

    const ARG_MODE_ARRAY = 0;
    const ARG_MODE_JSON = 1;
    /** @var int[] */
    protected $timings;
    /** @var int[] */
    protected $args = [];
    /** @var  int[] */
    protected $colors;
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var int */
    private $last_modification_date;

    /**
     * RgbDevice constructor. <b>Note:</b> Timings are interpreted as raw values input by user,
     * unless <code>$t_converted</code> is explicitly set to <code>true</code>
     * @param int $id - if -1 is provided as effect_id the next toDatabase call will assign it a new one
     * @param string $device_id
     * @param string $name
     * @param array $colors
     * @param array $timing
     * @param array $args
     * @param bool $t_json
     */
    public function __construct(int $id, array $colors, array $timing, array $args = array(),
                                string $name = null, int $last_mod_date = -1,
                                int $timing_mode = Effect::TIMING_MODE_SECONDS,
                                int $arg_mode = Effect::ARG_MODE_ARRAY
    ) {
        if($name === null)
            $this->name = Utils::getString("effect_default_name");
        else
            $this->name = $name;
        $this->id = $id;
        $this->colors = $colors;

        if($last_mod_date = -1)
            $this->last_modification_date = time();
        else
            $this->last_modification_date = $last_mod_date;

        if($timing_mode !== Effect::TIMING_MODE_JSON) {
            for($i = 0; $i < 6; $i++) {
                if(!isset($timing[$i]))
                    $timing[$i] = 0;
            }
        }

        switch($timing_mode) {
            case Effect::TIMING_MODE_JSON:
                $this->setTimings($this->timingJsonToArray($timing));
                break;
            case Effect::TIMING_MODE_RAW:
                $this->setTimingsRaw($timing);
                break;
            case Effect::TIMING_MODE_SECONDS:
                $this->setTimings($timing);
                break;
            default:
                throw new InvalidArgumentException("Invalid timing_mode: $timing_mode");
        }

        if($arg_mode === Effect::ARG_MODE_ARRAY) {
            $this->unpackArgs($args);
        } else {
            $this->args = $args;
        }

        $this->overwriteValues();
    }

    public function setTimings(array $timing) {
        $t = [];
        foreach($timing as $i => $value) {
            $t[$i] = Effect::convertToTiming($timing[$i]);
        }
        $this->setTimingsRaw($t);
    }

    public static function convertToTiming($float) {
        if($float < 0)
            return 0;
        foreach(self::getTimings() as $i => $timing) {
            if($float < $timing) return $i - 1;
        }
        return 255;
    }

    public static function getTimings() {
        $a = array();
        for($i = 0; $i < 256; $i++) {
            $a[$i] = self::getSeconds($i);
        }
        return $a;
    }

    public static function getSeconds(int $x) {
        if($x < 0 || $x > 255) {
            throw new InvalidArgumentException("x has to be an integer in range 0-255");
        }

        if($x <= 80) {
            return $x / 16;
        }
        if($x <= 120) {
            return $x / 8 - 5;
        }
        if($x <= 160) {
            return $x / 2 - 50;
        }
        if($x <= 190) {
            return $x - 130;
        }
        if($x <= 235) {
            return 2 * $x - 320;
        }
        if($x <= 245) {
            return 15 * $x - 3375;
        }
        return 60 * $x - 14400;
    }

    public function setTimingsRaw(array $timing) {
        if($timing[0] > 255 || $timing[0] < 0 || $timing[1] > 255 || $timing[1] < 0 ||
            $timing[2] > 255 || $timing[2] < 0 || $timing[3] > 255 || $timing[3] < 0 ||
            $timing[4] > 255 || $timing[4] < 0 || $timing[5] > 255 || $timing[5] < 0) {
            throw new InvalidArgumentException("Timings have to be in range 0-255");
        }

        $zeros = true;
        foreach($timing as $item) {
            if($item > 0)
                $zeros = false;
        }
        if($zeros)
            $timing[2] = 1;

        $this->timings[0] = $timing[0];
        $this->timings[1] = $timing[1];
        $this->timings[2] = $timing[2];
        $this->timings[3] = $timing[3];
        $this->timings[4] = $timing[4];
        $this->timings[5] = $timing[5];
    }

    public function timingJsonToArray($json) {
        $arr = [];
        $strings = $this->getTimingStrings();
        foreach($json as $key => $value) {
            $array_search = array_search($key, $strings);
            if($array_search !== false)
                $arr[$array_search] = $value;
        }
        return $arr;
    }

    protected function getTimingStrings() {
        return ["off", "fadein", "on", "fadeout", "rotation", "offset"];
    }

    public abstract function unpackArgs(array $args);

    /**
     * Makes sure the submitted values aren't going to cause a crash by overwriting invalid user input
     * The updated_effect JSON filed then contains those values and replaces them in the user interface
     */
    public abstract function overwriteValues();

    /**
     * @param int $color_limit
     * @return string
     */
    public function colorsHtml(int $color_limit) {
        $colors_html = "";
        if($this->getMinColors() === 0)
            return null;

        $max = $this->getMaxColors() === Effect::COLOR_COUNT_UNLIMITED ? min(sizeof($this->colors), $color_limit) :
            min(min(sizeof($this->colors), $color_limit), $this->getMaxColors());
        for($i = 0; $i < max($this->getMinColors(), $max); $i++) {
            $c = isset($this->colors[$i]) ? $this->colors[$i] : 0xff0000;
            $c_str = Utils::intToHex($c, 3);
            $template = self::COLOR_TEMPLATE;
            $template = str_replace("\$label", "color-$i", $template);
            $template = str_replace("\$color", "#" . $c_str, $template);
            $template = str_replace("\$title_delete", Utils::getString("profile_btn_hint_delete"), $template);
            $template = str_replace("\$title_jump", Utils::getString("profile_btn_hint_jump"), $template);
            $template = str_replace("\$disabled",
                sizeof($this->getColors()) <= $this->getMinColors() ? "disabled" : "",
                $template);
            $colors_html .= $template;
        }

        return $colors_html;
    }

    /**
     * @return int
     */
    public abstract function getMinColors();

    /**
     * @return int
     */
    public abstract function getMaxColors();

    public function getColors() {
        return $this->colors;
    }

    public function timingArgHtml() {
        $html = "";

        $timings = $this->getTimingsForEffect();
        $timing_strings = $this->getTimingStrings();
        $profile_timing = Utils::getString("profile_timing");
        $profile_arguments = Utils::getString("profile_arguments");

        $arguments_html = "";
        $timing_html = "";

        if(sizeof($this->args) > 0) {
            foreach($this->args as $name => $value0) {
                $arguments_html .= $this->getArgumentClass($name)->toString();
            }
        }

        for($i = 0; $i < 6; $i++) {
            $t = self::getSeconds($this->timings[$i]);
            if(($timings & (1 << $i)) > 0) {
                $template = self::INPUT_TEMPLATE_TIMES;
                $t_str = $timing_strings[$i];
                $template = str_replace("\$label", Utils::getString("profile_timing_$t_str"), $template);
                $template = str_replace("\$name", "time_" . $timing_strings[$i], $template);
                $template = str_replace("\$placeholder", $t, $template);
                $template = str_replace("\$value", $t, $template);
                $timing_html .= $template;
            } else {
                $template = self::HIDDEN_TEMPLATE;
                $template = str_replace("\$name", "time_" . $timing_strings[$i], $template);
                $template = str_replace("\$value", $t, $template);
                $timing_html .= $template;
            }
        }

        if($timings !== 0) {
            $html .= "<div class=\"timing-container col-24 col-sm-12 col-lg-8 mb-3 mb-sm-0\"><h4>$profile_timing</h4>
                        <div class=\"row mx-0\">$timing_html</div></div>";
        } else {
            $html .= "$timing_html";
        }
        if(sizeof($this->args) > 0)
            $html .= "<div class=\"args-container col-24 col-lg-8 col-xl-10\"><h4>$profile_arguments</h4>
                        <div class=\"row mx-0\">$arguments_html</div></div>";

        return $html;
    }

    /**
     * @return int
     */
    public abstract function getTimingsForEffect();

    /**
     * @param $name
     * @return Argument
     */
    public abstract function getArgumentClass($name);

    public function toJson() {
        $data = array();

        $data["times"] = $this->getTimes(Effect::TIMING_MODE_JSON);
        $data["colors"] = $this->colors;
        $data["effect"] = $this->getEffectId();
        $data["args"] = $this->args;
        $data["effect_id"] = $this->id;

        return $data;
    }

    /**
     * @return int[]
     */
    public function getTimes(int $mode = Effect::TIMING_MODE_SECONDS): array {
        switch($mode) {
            case Effect::TIMING_MODE_SECONDS:
                $arr = [];
                for($i = 0; $i < 6; $i++) {
                    $arr[$i] = Effect::getSeconds($this->timings[$i]);
                }
                return $arr;
            case Effect::TIMING_MODE_RAW:
                return $this->timings;
            case Effect::TIMING_MODE_JSON:
                return $this->timingArrayToJson();
            default:
                throw new InvalidArgumentException("Invalid mode: $mode");
        }
    }

    public function timingArrayToJson() {
        $arr = [];
        $strings = $this->getTimingStrings();
        foreach($this->timings as $i => $value) {
            $arr[$strings[$i]] = Effect::getSeconds($value);
        }
        return $arr;
    }

    /**
     * @return int
     */
    public abstract function getEffectId();

    /**
     * @return int
     */
    public abstract function avrEffect();

    public function getSanitizedColors(int $color_count) {
        $arr = $this->getColors();
        $args = [];
        for($i = 0; $i < $color_count; $i++) {
            $args[$i] = isset($arr[$i]) ? $arr[$i] : 0;
        }
        return $args;
    }

    public function toDatabase() {
        $args = $this->getSanitizedArgs();
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO devices_effects 
                (id, effect, name, time0, time1, time2, time3, time4, time5, 
                arg0, arg1, arg2, arg3, arg4, arg5) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE effect = ?, name = ?, time0 = ?, time1 = ?, time2 = ?, time3 = ?, time4 = ?, time5 = ?, 
                arg0 = ?, arg1 = ?, arg2 = ?, arg3 = ?, arg4 = ?, arg5 = ?";
        $stmt = $conn->prepare($sql);
        $effectId = $this->getEffectId();
        $id = $this->id >= 0 ? $this->id : null;
        $stmt->bind_param("iisiiiiiiiiiiiiisiiiiiiiiiiii", $id,
            $effectId, $this->name, $this->timings[0], $this->timings[1], $this->timings[2], $this->timings[3],
            $this->timings[4], $this->timings[5], $args[0], $args[1], $args[2], $args[3],
            $args[4], $args[5], $effectId, $this->name, $this->timings[0], $this->timings[1], $this->timings[2],
            $this->timings[3], $this->timings[4], $this->timings[5], $args[0], $args[1], $args[2],
            $args[3], $args[4], $args[5]
        );

        $stmt->execute();
        $this->id = $this->id >= 0 ? $this->id : (int)$conn->insert_id;
        $changes = $stmt->affected_rows > 0 ? true : false;
        $stmt->close();
        $this->saveColors();
        return $changes;
    }

    public function getSanitizedArgs() {
        $arr = $this->packArgs();
        $args = [];
        for($i = 0; $i < 6; $i++) {
            $args[$i] = isset($arr[$i]) ? $arr[$i] : 0;
        }
        return $args;
    }

    /**
     * @return array
     */
    public abstract function packArgs();

    private function saveColors() {
        $conn = DbUtils::getConnection();
        $sql = "DELETE FROM devices_colors WHERE effect_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $stmt->close();

        $sql = "INSERT INTO devices_colors (effect_id, color, `order`) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        foreach($this->colors as $i => $color) {
            $stmt->bind_param("iii", $this->id, $color, $i);
            try {
                $stmt->execute();
            }
            catch(mysqli_sql_exception $e) {

            }
        }
        $stmt->close();
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLastModificationDate(): int {
        return $this->last_modification_date;
    }

    public static function getColorTemplateLocalized() {
        $template = self::COLOR_TEMPLATE;
        $template = str_replace("\$title_delete", Utils::getString("profile_btn_hint_delete"), $template);
        $template = str_replace("\$title_jump", Utils::getString("profile_btn_hint_jump"), $template);
        $template = str_replace("\$color", "", $template);
        return $template;
    }

    /**
     * @param int $id
     * @return Effect
     */
    public static abstract function getDefault(int $id);

    public static function convertIncrementToTiming($float) {
        if($float < 0)
            return 0;
        foreach(self::getIncrementTimings() as $i => $timing) {
            if($float < $timing) return $i - 1;
        }
        return 255;
    }

    public static function getIncrementTimings() {
        $a = array();
        for($i = 0; $i < 256; $i++) {
            $a[$i] = self::getIncrementTiming($i);
        }
        return $a;
    }

    public static function getIncrementTiming(int $x) {
        if($x < 0 || $x > 255) {
            throw new InvalidArgumentException("x has to be an integer in range 0-255");
        }

        if($x <= 60) {
            return $x / 2;
        }
        if($x <= 90) {
            return $x - 30;
        }
        if($x <= 126) {
            return 5 * $x / 2 - 165;
        }
        if($x <= 156) {
            return 5 * $x - 480;
        }
        if($x <= 196) {
            return 15 * $x - 2040;
        }
        if($x <= 211) {
            return 60 * $x - 10860;
        }
        if($x <= 253) {
            return 300 * $x - 61500;
        }
        if($x == 254) return 18000;
        return 21600;
    }

    public static function forProfile(int $profile_id) {
        $conn = DbUtils::getConnection();
        $colors = Effect::getColorsForEffectIdsByProfileId($profile_id);
        $sql = "SELECT devices_effects.id, last_modified, name, effect, time0, time1, time2, time3, time4, time5, 
                arg0, arg1, arg2, arg3, arg4, arg5
                FROM devices_effect_scenes_effect_join 
                  JOIN devices_effect_join dde on devices_effect_scenes_effect_join.effect_join_id = dde.id
                  JOIN devices_effects on dde.effect_id = devices_effects.id
                WHERE devices_effect_scenes_effect_join.scene_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $profile_id);
        $arr = Effect::arrayFromStatement($stmt, $colors, true);
        $stmt->close();
        return $arr;
    }

    private static function getColorsForEffectIdsByProfileId(int $profile_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT devices_effects.id FROM devices_effect_join
                  JOIN devices_effects ON devices_effect_join.effect_id = devices_effects.id
                  JOIN devices_effect_scenes_effect_join dep on devices_effect_join.id = dep.effect_join_id
                WHERE dep.scene_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $profile_id);
        $arr = Effect::getEffectIdsColorsFromStatement($stmt);
        $stmt->close();
        return $arr;
    }

    private static function getEffectIdsColorsFromStatement(mysqli_stmt & $stmt) {
        $stmt->bind_result($effect_id);
        $stmt->execute();
        $ids = [];
        while($stmt->fetch()) {
            $ids[] = $effect_id;
        }

        $arr = [];
        foreach($ids as $id) {
            $arr[$id] = Effect::getColorsForEffect($id);
        }
        return $arr;
    }

    public static function getColorsForEffect(int $effect_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT color, `order` FROM devices_colors WHERE effect_id = ? ORDER BY `order` ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $effect_id);
        $stmt->bind_result($color, $order);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            $arr[] = $color;
        }
        $stmt->close();
        return $arr;
    }

    /**
     * Columns required in order:
     * id, name, effect, time0, time1, time2, time3, time4, time5, arg0, arg1, arg2, arg3, arg4, arg5
     * @param mysqli_stmt $stmt
     * @param array $colors
     * @return Effect[]
     */
    private static function arrayFromStatement(mysqli_stmt & $stmt, array $colors, bool $assoc = false) {
        $stmt->bind_result($id, $d, $n, $e, $t0, $t1, $t2, $t3, $t4, $t5, $a0, $a1, $a2, $a3, $a4, $a5);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            $class = Effect::getClassForEffectId($e);
            if(!class_exists($class) || !is_subclass_of($class, Effect::class))
                throw new InvalidArgumentException("$class is not a valid Effect class name");

            if($assoc) {
                $arr[$id] = new $class($id, $colors[$id],
                    [$t0, $t1, $t2, $t3, $t4, $t5],
                    [$a0, $a1, $a2, $a3, $a4, $a5], $n, strtotime($d),
                    Effect::TIMING_MODE_RAW);
            } else {
                $arr[] = new $class($id, $colors[$id],
                    [$t0, $t1, $t2, $t3, $t4, $t5],
                    [$a0, $a1, $a2, $a3, $a4, $a5], $n, strtotime($d),
                    Effect::TIMING_MODE_RAW);
            }
        }
        return $arr;
    }

    /**
     * @param int $id
     * @return string
     */
    private static function getClassForEffectId(int $id) {
        switch($id) {
            case Effect::EFFECT_OFF:
                return Off::class;
            case Effect::EFFECT_STATIC:
                return Statiic::class;
            case Effect::EFFECT_BREATHING:
                return Breathe::class;
            case Effect::EFFECT_FADING:
                return Fade::class;
            case Effect::EFFECT_BLINKING:
                return Blink::class;
            case Effect::EFFECT_PIECES:
                return Pieces::class;
            case Effect::EFFECT_SPECTRUM:
                return Spectrum::class;
            case Effect::EFFECT_SIMPLE_RAINBOW:
                return SimpleRainbow::class;
            case Effect::EFFECT_ROTATING_RAINBOW:
                return RotatingRainbow::class;
            case Effect::EFFECT_PARTICLES:
                return Particles::class;
            case Effect::EFFECT_FILL:
                return Fill::class;
            default:
                throw new UnexpectedValueException("Invalid effect id: $id");
        }
    }

    public static function forDevice(string $device_id) {
        $conn = DbUtils::getConnection();
        $colors = Effect::getColorsForEffectIdsByDeviceId($device_id);
        $sql = "SELECT devices_effects.id, last_modified, name, effect, time0, time1, time2, time3, time4, time5, 
                arg0, arg1, arg2, arg3, arg4, arg5
                FROM devices_effect_join 
                  JOIN devices_effects ON devices_effect_join.effect_id = devices_effects.id 
                WHERE device_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $device_id);
        $arr = Effect::arrayFromStatement($stmt, $colors);
        $stmt->close();
        return $arr;
    }

    private static function getColorsForEffectIdsByDeviceId(string $device_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT devices_effects.id FROM devices_effect_join 
                  JOIN devices_effects ON devices_effect_join.effect_id = devices_effects.id 
                WHERE device_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $device_id);
        $arr = Effect::getEffectIdsColorsFromStatement($stmt);
        $stmt->close();
        return $arr;
    }

    public static function getDefaultForNewEffect(int $effect) {
        $class = Effect::getClassForEffectId($effect);
        if(!class_exists($class) || !is_subclass_of($class, Effect::class))
            throw new InvalidArgumentException("$class is not a valid Effect class name");
        /** @noinspection PhpUndefinedMethodInspection */
        return $class::getDefault(-1);
    }

    public static function getDefaultForEffectId(int $effect_id, int $effect) {
        $class = Effect::getClassForEffectId($effect);
        if(!class_exists($class) || !is_subclass_of($class, Effect::class))
            throw new InvalidArgumentException("$class is not a valid Effect class name");
        /** @noinspection PhpUndefinedMethodInspection */
        return $class::getDefault($effect_id);
    }

    /**
     * @param array $json
     * @return Effect
     */
    public static function fromJson(array $json) {
        $times = $json["times"];
        $args = $json["args"];
        $colors = $json["colors"];
        $name = $json["effect_name"];
        $id = $json["effect_id"];

        $class = Effect::getClassForEffectId($json["effect"]);
        if(!class_exists($class) || !is_subclass_of($class, Effect::class))
            throw new InvalidArgumentException("$class is not a valid Effect class name");
        return new $class($id, $colors, $times, $args, $name, time(), Effect::TIMING_MODE_JSON, Effect::ARG_MODE_JSON);
    }
}