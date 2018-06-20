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
 * Time: 13:43
 */

require_once __DIR__ . "/../database/DbUtils.php";

abstract class Effect
{
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
    const EFFECT_RAINBOW = 5;
    const EFFECT_FILLING = 6;
    const EFFECT_MARQUEE = 7;
    const EFFECT_ROTATING = 8;
    const EFFECT_SWEEP = 9;
    const EFFECT_ANDROID_PB = 10;
    const EFFECT_DOUBLE_FILL = 12;
    const EFFECT_HIGHS = 13;
    const EFFECT_SOURCES = 14;
    const EFFECT_PIECES = 15;
    const EFFECT_RAINBOW_ROTATING = 16;
    const EFFECT_FILLING_FADE = 17;
    const EFFECT_SPECTRUM = 18;
    const EFFECT_TWO_HALVES = 19;
    const EFFECT_TWO_HALVES_FADE = 20;
    const EFFECT_PARTICLES = 21;

    const DIRECTION_CW = 0;
    const DIRECTION_CCW = 1;

    const TIME_OFF = 0;
    const TIME_FADEIN = 1;
    const TIME_ON = 2;
    const TIME_FADEOUT = 3;
    const TIME_ROTATION = 4;
    const TIME_DELAY = 5;

    const COLOR_COUNT_UNLIMITED = -1;

    const /** @noinspection CssInvalidPropertyValue */
        COLOR_TEMPLATE =
        "<div class=\"color-container row mb-1\">
            <div class=\"col-auto ml-3\">
                <button class=\"btn btn-danger color-delete-btn\" type=\"button\" role=\"button\" title=\"\$title_delete\"><span class=\"oi oi-trash\"></span></button>
            </div>
            <div class=\"col-auto ml-1\">
                <button class=\"btn color-jump-btn\" type=\"button\" role=\"button\" title=\"\$title_jump\"><span class=\"oi oi-action-redo\"></span></button>
            </div>
            <div class=\"col pl-1\">
                <div class=\"input-group colorpicker-component\">
                    <input type=\"text\" class=\"form-control color-input\" value=\"\$color\" autocomplete=\"off\" 
                    aria-autocomplete=\"none\" spellcheck=\"false\"/>
                    <span class=\"input-group-addon color-swatch-handle\"><i></i></span>
                </div>
            </div>
        </div>";

    const INPUT_TEMPLATE_ARGUMENTS = "<div class=\"col-sm-6 col-md-6 col-lg-4 col-xl-3 form-group px-1 mb-1\"><label class=\"mb-0\">\$label</label>
                            <input class=\"form-control\" type=\"text\" name=\"\$name\" 
                                    placeholder=\"\$placeholder\" value=\"\$value\"></div>";
    const INPUT_TEMPLATE_TIMES = "<div class=\"col-sm-6 col-md-6 col-lg-4 col-xl-3 form-group px-1 mb-1\"><label class=\"mb-0\">\$label</label>
                            <input class=\"form-control\" type=\"text\" name=\"\$name\" placeholder=\"\$placeholder\"
                             value=\"\$value\"></div>";

    const HIDDEN_TEMPLATE = "<input type=\"hidden\" name=\"\$name\" value=\"\$value\">";

    /** @var int */
    private $id;

    /** @var string */
    private $device_id;

    /** @var int[] */
    protected $timings;

    /** @var int[] */
    protected $args;

    /** @var  int[] */
    private $colors;

    /**
     * RgbDevice constructor. <b>Note:</b> Timings are interpreted as raw values input by user,
     * unless <code>$t_converted</code> is explicitly set to <code>true</code>
     * @param int $id
     * @param string $device_id
     * @param array $colors
     * @param array $timing
     * @param array $args
     * @param bool $t_converted
     */
    public function __construct(int $id, string $device_id, array $colors, array $timing, array $args = array(), bool $t_converted = false
    )
    {
        $this->id = $id;
        $this->device_id = $device_id;
        $this->colors = $colors;
        $t_converted ? $this->setTimings($timing) : $this->setTimingsRaw($timing);
        $this->args = $args;
    }

    /**
     * @param string $color
     * @return bool|int - false if to many colors in the array, otherwise number of colors in the array
     */
    public function addColor(string $color)
    {
        if(sizeof($this->colors) >= 16)
            return false;
        return array_push($this->colors, $color);
    }

    public function removeColor(int $pos)
    {
        unset($this->colors[$pos]);
    }

    public function getColors()
    {
        return $this->colors;
    }

    public function setTimingsRaw(array $timing)
    {
        $t = [];
        foreach($timing as $i => $value)
        {
            $t[$i] = Effect::convertToTiming($timing[$i]);
        }
        $this->setTimings($t);
    }

    public function setTimings(array $timing)
    {
        if($timing[0] > 255 || $timing[0] < 0 || $timing[1] > 255 || $timing[1] < 0 ||
            $timing[2] > 255 || $timing[2] < 0 || $timing[3] > 255 || $timing[3] < 0 ||
            $timing[4] > 255 || $timing[4] < 0 || $timing[5] > 255 || $timing[5] < 0)
        {
            throw new InvalidArgumentException("Timings have to be in range 0-255");
        }

        $this->timings[0] = $timing[0];
        $this->timings[1] = $timing[1];
        $this->timings[2] = $timing[2];
        $this->timings[3] = $timing[3];
        $this->timings[4] = $timing[4];
        $this->timings[5] = $timing[5];
    }

    /**
     * @param int $color_limit
     * @return string
     */
    public function colorsHtml(int $color_limit)
    {
        $colors_html = "";

        for($i = 0; $i < min(sizeof($this->getColors(), $color_limit)); $i++)
        {
            $template = self::COLOR_TEMPLATE;
            $template = str_replace("\$active", $i == 0 ? "checked" : "", $template);
            $template = str_replace("\$label", "color-$i", $template);
            $template = str_replace("\$color", "#" . strtolower($this->getColors()[$i]), $template);
            $template = str_replace("\$title_delete", Utils::getString("profile_btn_hint_delete"), $template);
            $template = str_replace("\$title_jump", Utils::getString("profile_btn_hint_jump"), $template);
            $colors_html .= $template;
        }

        return $colors_html;
    }

    public function timingArgHtml()
    {
        $html = "";

        $timings = $this->getTimingsForEffect() | (1 << Effect::TIME_DELAY);
        $timing_strings = $this->getTimingStrings();
        $profile_timing = Utils::getString("profile_timing");
        $profile_arguments = Utils::getString("profile_arguments");

        $arguments_html = "";
        $timing_html = "";

        if(sizeof($this->args) > 0)
        {
            foreach($this->args as $name => $argument)
            {
                switch($name)
                {
                    case "direction":
                        $str_cw = Utils::getString("profile_direction_cw");
                        $str_ccw = Utils::getString("profile_direction_ccw");
                        $str = Utils::getString("profile_arguments_" . $name);
                        $selected0 = $argument ? " selected" : "";
                        $selected1 = $argument ? "" : " selected";
                        $arguments_html .= "<div class=\"col-auto px-1\"><label class=\"mb-0\">$str</label>
                                            <select class=\"form-control\" name=\"arg_$name\">
                                                <option value=\"" . Effect::DIRECTION_CW . "\"$selected0>$str_cw</option>
                                                <option value=\"" . Effect::DIRECTION_CCW . "\"$selected1>$str_ccw</option>
                                            </select></div>";
                        break;
                    case "smooth":
                    case "fade_smooth":
                    case "fill_fade_return":
                    case "two_halves_return":
                        $str_yes = Utils::getString("yes");
                        $str_no = Utils::getString("no");
                        $str = Utils::getString("profile_arguments_" . $name);
                        $selected0 = $argument ? " selected" : "";
                        $selected1 = $argument ? "" : " selected";
                        $arguments_html .= "<div class=\"col-auto px-1\"><label class=\"mb-0\">$str</label>
                                            <select class=\"form-control\" name=\"arg_$name\">
                                                <option value=\"" . 1 . "\"$selected0>$str_yes</option>
                                                <option value=\"" . 0 . "\"$selected1>$str_no</option>
                                            </select></div>";
                        break;
                    case "two_halves_color_count":
                        $str = Utils::getString("profile_arguments_" . $name);
                        $selected0 = $argument === 2 ? " selected" : "";
                        $selected1 = $argument === 1 ? "" : " selected";
                        $arguments_html .= "<div class=\"col-auto px-1\"><label class=\"mb-0\">$str</label>
                                            <select class=\"form-control\" name=\"arg_$name\">
                                                <option value=\"" . 1 . "\"$selected0>1</option>
                                                <option value=\"" . 2 . "\"$selected1>2</option>
                                            </select></div>";
                        break;
                    default:
                        $template = self::INPUT_TEMPLATE_ARGUMENTS;
                        $template = str_replace("\$label", Utils::getString("profile_arguments_$name"), $template);
                        $template = str_replace("\$name", "arg_" . $name, $template);
                        $template = str_replace("\$placeholder", $argument, $template);
                        $template = str_replace("\$value", $argument, $template);
                        $arguments_html .= $template;
                }
            }
        }

        for($i = 0; $i < 6; $i++)
        {
            if(($timings & (1 << (5 - $i))) > 0)
            {
                $t = self::getTiming($this->timings[$i]);
                $template = self::INPUT_TEMPLATE_TIMES;
                $template = str_replace("\$label", Utils::getString("profile_timing_$timing_strings[i]"), $template);
                $template = str_replace("\$name", "time_" . $timing_strings[$i], $template);
                $template = str_replace("\$placeholder", $t, $template);
                $template = str_replace("\$value", $t, $template);
                $timing_html .= $template;
            }
            else
            {
                $template = self::HIDDEN_TEMPLATE;
                $template = str_replace("\$name", "time_" . $timing_strings[$i], $template);
                $template = str_replace("\$value", 0, $template);
                $timing_html .= $template;
            }
        }

        if($timings != 0)
        {
            $html .= "<div class=\"timing-container col-12 col-sm-6 col-lg-4 mb-3 mb-sm-0\"><h3>$profile_timing</h3>
                        <div class=\"row mx-0\">$timing_html</div></div>";
        }
        else
        {
            $html .= "$timing_html";
        }
        if(sizeof($this->args) > 0)
            $html .= "<div class=\"args-container col-12 col-lg-4 col-xl-5\"><h3>$profile_arguments</h3>
                        <div class=\"row mx-0\">$arguments_html</div></div>";

        return $html;
    }

    public function toJson()
    {
        $data = array();

        $data["color_count"] = sizeof($this->colors);
        $data["times"] = $this->timings;
        $data["colors"] = $this->colors;
        $data["color_cycles"] = isset($this->args["color_cycles"]) ? $this->args["color_cycles"] : 1;
        $data["effect"] = $this->avrEffect();
        $data["args"] = $this->argsToArray();

        return $data;
    }

    protected function getTimingStrings()
    {
        return ["off", "fadein", "on", "fadeout", "rotation", "offset"];
    }

    /**
     * @return int
     */
    public abstract function getTimingsForEffect();

    /**
     * @return array
     */
    public abstract function argsToArray();

    /**
     * @return array
     */
    public abstract function argList();

    /**
     * @return int
     */
    public abstract function avrEffect();

    /**
     * @return int
     */
    public abstract function getEffectId();

    /**
     * @return int
     */
    public abstract function getMaxColors();

    /**
     * @return int
     */
    public abstract function getMinColors();

    /**
     * @param string $device_id
     * @return Effect
     */
    public static abstract function getDefault(string $device_id);

    public static function getTiming(int $x)
    {
        if($x < 0 || $x > 255)
        {
            throw new InvalidArgumentException("x has to be an integer in range 0-255");
        }

        if($x <= 80)
        {
            return $x / 16;
        }
        if($x <= 120)
        {
            return $x / 8 - 5;
        }
        if($x <= 160)
        {
            return $x / 2 - 50;
        }
        if($x <= 190)
        {
            return $x - 130;
        }
        if($x <= 235)
        {
            return 2 * $x - 320;
        }
        if($x <= 245)
        {
            return 15 * $x - 3375;
        }
        return 60 * $x - 14400;
    }

    public static function convertToTiming($float)
    {
        if($float < 0)
            return 0;
        foreach(self::getTimings() as $i => $timing)
        {
            if($float < $timing) return $i - 1;
        }
        return 255;
    }

    public static function getTimings()
    {
        $a = array();
        for($i = 0; $i < 256; $i++)
        {
            $a[$i] = self::getTiming($i);
        }
        return $a;
    }

    public static function getIncrementTiming(int $x)
    {
        if($x < 0 || $x > 255)
        {
            throw new InvalidArgumentException("x has to be an integer in range 0-255");
        }

        if($x <= 60)
        {
            return $x / 2;
        }
        if($x <= 90)
        {
            return $x - 30;
        }
        if($x <= 126)
        {
            return 5 * $x / 2 - 165;
        }
        if($x <= 156)
        {
            return 5 * $x - 480;
        }
        if($x <= 196)
        {
            return 15 * $x - 2040;
        }
        if($x <= 211)
        {
            return 60 * $x - 10860;
        }
        if($x <= 253)
        {
            return 300 * $x - 61500;
        }
        if($x == 254) return 18000;
        return 21600;
    }

    public static function convertIncrementToTiming($float)
    {
        if($float < 0)
            return 0;
        foreach(self::getIncrementTimings() as $i => $timing)
        {
            if($float < $timing) return $i - 1;
        }
        return 255;
    }

    public static function getIncrementTimings()
    {
        $a = array();
        for($i = 0; $i < 256; $i++)
        {
            $a[$i] = self::getIncrementTiming($i);
        }
        return $a;
    }

    public function toDatabase(int $profile_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO devices_effects 
                (id, profile_id, device_id, effect, time0, time1, time2, time3, time4, time5, 
                arg0, arg1, arg2, arg3, arg4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE effect = ?, time0 = ?, time1 = ?, time2 = ?, time3 = ?, time4 = ?, time5 = ?, 
                arg0 = ?, arg2 = ?, arg3 = ?, arg4 = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisiiiiiiiiiiiiiiiiiiiiiiii",$this->id, $profile_id, $this->device_id,
            $this->getEffectId(), $this->timings[0], $this->timings[1], $this->timings[2], $this->timings[3],
            $this->timings[4], $this->timings[5], $this->args[0], $this->args[1], $this->args[2], $this->args[3],
            $this->args[4], $this->getEffectId(), $this->timings[0], $this->timings[1], $this->timings[2],
            $this->timings[3], $this->timings[4], $this->timings[5], $this->args[0], $this->args[1], $this->args[2],
            $this->args[3], $this->args[4]
        );
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function getColorsForEffect(int $effect_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT color FROM devices_colors WHERE effect_id = ? ORDER BY `order` ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $effect_id);
        $stmt->bind_result($color);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $arr[] = $color;
        }
        $stmt->close();
        return $arr;
    }

    public static function forProfile(int $profile_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, device_id, effect, time0, time1, time2, time3, time4, time5, arg0, arg1, arg2, arg3, arg4 
                FROM devices_effects WHERE profile_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $profile_id);
        $stmt->bind_result($id, $d_id, $e, $t0, $t1, $t2, $t3, $t4, $t5, $a0, $a1, $a2, $a3, $a4);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $c = Effect::getColorsForEffect($id);
            switch($e)
            {
                case Effect::EFFECT_OFF:
                    $arr[] = new Off($id, $d_id, $c, [$t0, $t1, $t2, $t3, $t4, $t5], [$a0, $a1, $a2, $a3, $a4], true);
                    break;
                case Effect::EFFECT_STATIC:
                    $arr[] = new Fixed($id, $d_id, $c, [$t0, $t1, $t2, $t3, $t4, $t5], [$a0, $a1, $a2, $a3, $a4], true);
                    break;
                case Effect::EFFECT_BREATHING:
                    $arr[] = new Breathe($id, $d_id, $c, [$t0, $t1, $t2, $t3, $t4, $t5], [$a0, $a1, $a2, $a3, $a4], true);
                    break;
                default:
                    throw new UnexpectedValueException("Invalid effect id: $e");
            }
        }
        $stmt->close();
        return $arr;
    }
}