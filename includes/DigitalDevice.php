<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:48
 */
class DigitalDevice extends Device
{
    const EFFECT_OFF = 100;
    const EFFECT_STATIC = 101;
    const EFFECT_BREATHING = 102;
    const EFFECT_BLINKING = 103;
    const EFFECT_FADING = 104;
    const EFFECT_RAINBOW = 105;
    const EFFECT_FILLING = 106;
    const EFFECT_MARQUEE = 107;
    const EFFECT_ROTATING = 108;
    const EFFECT_SWEEP = 109;
    const EFFECT_ANDROID_PB = 110;
    const EFFECT_DOUBLE_FILL = 112;
    const EFFECT_HIGHS = 113;
    const EFFECT_SOURCES = 114;
    const EFFECT_PIECES = 115;
    const EFFECT_RAINBOW_ROTATING = 116;
    const EFFECT_FILLING_FADE = 117;
    const EFFECT_SPECTRUM = 118;
    const EFFECT_TWO_HALVES = 119;
    const EFFECT_TWO_HALVES_FADE = 120;
    const EFFECT_PARTICLES = 121;
    const EFFECT_DEMO = 199;

    const DIRECTION_CW = 1;
    const DIRECTION_CCW = 0;

    /**
     * DigitalDevice constructor.
     * @param array $colors
     * @param int $effect
     * @param float|int $off
     * @param float|int $fadein
     * @param float|int $on
     * @param float|int $fadeout
     * @param float|int $rotating
     * @param float|int $offset
     * @param array $args
     */
    public function __construct(array $colors, int $effect, float $off, float $fadein, float $on, float $fadeout,
                                float $rotating, float $offset, array $args = array())
    {
        if($effect === self::EFFECT_OFF || $effect === self::EFFECT_STATIC)
            $on = 1;
        parent::__construct($colors, $effect, $off, $fadein, $on, $fadeout, $rotating, $offset, $args);
    }

    /**
     * Order of bits: Off, Fade-in, On, Fade-out, Rotation, Offset
     * @return int
     */
    public function getTimingsForEffect()
    {
        switch($this->effect)
        {
            case self::EFFECT_OFF:
                return 0b000000;
            case self::EFFECT_STATIC:
                return 0b000000;
            case self::EFFECT_BREATHING:
                return 0b111101;
            case self::EFFECT_BLINKING:
                return 0b101001;
            case self::EFFECT_FADING:
                return 0b001101;
            case self::EFFECT_RAINBOW_ROTATING:
                return 0b000011;
            case self::EFFECT_RAINBOW:
                return 0b000101;
            case self::EFFECT_FILLING:
                return 0b111111;
            case self::EFFECT_FILLING_FADE:
                return 0b001111;
            case self::EFFECT_MARQUEE:
                return 0b001111;
            case self::EFFECT_ROTATING:
                return 0b001111;
            case self::EFFECT_SWEEP:
                return 0b111111;
            case self::EFFECT_ANDROID_PB:
                return 0b111001;
            case self::EFFECT_TWO_HALVES:
                return 0b111101;
            case self::EFFECT_TWO_HALVES_FADE:
                return 0b001101;
            case self::EFFECT_DOUBLE_FILL:
                return 0b111001;
            case self::EFFECT_HIGHS:
                return 0b010001;
            case self::EFFECT_SOURCES:
                return 0b010001;
            case self::EFFECT_PIECES:
                return 0b001111;
            case self::EFFECT_SPECTRUM:
                return 0b001111;
            case self::EFFECT_PARTICLES:
                return 0b111001;
            case self::EFFECT_DEMO:
                return 0b000001;
            default:
                return 0b0000;
        }
    }

    public function colorLimit()
    {
        switch($this->effect)
        {
            case self::EFFECT_OFF:
            case self::EFFECT_RAINBOW:
            case self::EFFECT_RAINBOW_ROTATING:
                return 0;
            case self::EFFECT_STATIC:
                return 1;
            default:
                return 16;
        }
    }

    public function avrEffect()
    {
        switch($this->effect)
        {
            case self::EFFECT_OFF:
            case self::EFFECT_STATIC:
            case self::EFFECT_BREATHING:
            case self::EFFECT_BLINKING:
                return self::AVR_EFFECT_BREATHE;

            case self::EFFECT_FADING:
                return self::AVR_EFFECT_FADE;

            case self::EFFECT_RAINBOW:
            case self::EFFECT_RAINBOW_ROTATING:
                return self::AVR_EFFECT_RAINBOW;

            case self::EFFECT_MARQUEE:
            case self::EFFECT_ROTATING:
                return self::AVR_EFFECT_ROTATING;

            case self::EFFECT_PIECES:
                return self::AVR_EFFECT_PIECES;

            case self::EFFECT_FILLING:
            case self::EFFECT_TWO_HALVES:
                return self::AVR_EFFECT_FILL;

            case self::EFFECT_FILLING_FADE:
            case self::EFFECT_TWO_HALVES_FADE:
                return self::AVR_EFFECT_FILLING_FADE;

            case self::EFFECT_SPECTRUM:
                return self::AVR_EFFECT_SPECTRUM;

            case self::EFFECT_PARTICLES:
                return self::AVR_EFFECT_PARTICLES;

            default:
                throw new InvalidArgumentException("Unknown effect: " . $this->effect);
        }
    }

    public function argsToArray()
    {
        $array = array(0, 0, 0, 0, 0);

        switch($this->effect)
        {
            case self::EFFECT_BREATHING:
                {
                    $array[1] = $this->args["breathe_min_val"];
                    $array[2] = $this->args["breathe_max_val"];
                    break;
                }
            case self::EFFECT_FILLING:
            case self::EFFECT_FILLING_FADE:
                {
                    if(isset($this->args["fade_smooth"]))
                    {
                        $fade_smooth = $this->args["fade_smooth"];
                    }
                    else
                    {
                        $fade_smooth = 0;
                    }
                    $array[0] = ($this->args["direction"] << 0) | ($this->args["smooth"] << 1) |
                        ($this->args["fill_fade_return"] << 2) | ($fade_smooth << 3);
                    $array[1] = $this->args["fill_fade_color_count"];
                    $array[2] = $this->args["fill_fade_piece_count"];
                    $array[3] = $this->args["fill_fade_direction1"];
                    $array[4] = $this->args["fill_fade_direction2"];
                    break;
                }
            case self::EFFECT_STATIC:
            case self::EFFECT_BLINKING:
                {
                    $array[1] = 0;
                    $array[2] = 255;
                    break;
                }
            case self::EFFECT_MARQUEE:
                {
                    $array[0] = ($this->args["direction"] << 0) | ($this->args["smooth"] << 1);
                    $array[1] = 1;
                    $array[2] = 4;
                    $array[3] = 2;
                    break;
                }
            case self::EFFECT_ROTATING:
                {
                    $array[0] = ($this->args["direction"] << 0) | ($this->args["smooth"] << 1);
                    $array[1] = $this->args["rotating_color_count"];
                    $array[2] = $this->args["rotating_element_count"];
                    $array[3] = $this->args["rotating_led_count"];
                    break;
                }
            case self::EFFECT_PIECES:
                {
                    $array[0] = ($this->args["direction"] << 0) | ($this->args["smooth"] << 1);
                    $array[1] = $this->args["pieces_color_count"];
                    $array[2] = $this->args["pieces_piece_count"];
                    break;
                }
            case self::EFFECT_SPECTRUM:
                {
                    $array[0] = ($this->args["direction"] << 0);
                    $array[1] = $this->args["spectrum_color_count"];
                    $array[2] = $this->args["spectrum_modes"];
                    break;
                }
            case self::EFFECT_RAINBOW:
                {
                    $array[0] = (1 << 3);
                    $array[1] = $this->args["rainbow_brightness"];
                    break;
                }
            case self::EFFECT_RAINBOW_ROTATING:
                {
                    $array[0] = ($this->args["direction"] << 0) | ($this->args["rainbow_mode"] << 2);
                    $array[1] = $this->args["rainbow_brightness"];
                    $array[2] = $this->args["rainbow_sources"];
                    break;
                }
            case self::EFFECT_TWO_HALVES:
            case self::EFFECT_TWO_HALVES_FADE:
                {
                    if(isset($this->args["fade_smooth"]))
                    {
                        $fade_smooth = $this->args["fade_smooth"];
                    }
                    else
                    {
                        $fade_smooth = 0;
                    }
                    $array[0] = ($this->args["direction"] << 0) | ($this->args["smooth"] << 1) |
                        ($this->args["two_halves_return"] << 2) | ($fade_smooth << 3);
                    $array[1] = $this->args["two_halves_color_count"];
                    $array[2] = 2;
                    $array[3] = $this->args["direction"] ? 1 : 2;
                    $array[4] = 0;
                    break;
                }
            case self::EFFECT_PARTICLES:
                {
                    $array[0] = ($this->args["direction"] << 0) | ($this->args["smooth"] << 1);
                    $array[1] = $this->args["particles_size"];
                    break;
                }
        }

        return $array;
    }

    public static function _off()
    {
        return self::off();
    }

    public static function off()
    {
        return new self(array("000000"), self::EFFECT_OFF, 0, 0, 1, 0, 0, 0);
    }

    public static function _static()
    {
        return self::static (array("FFFFFF"));
    }

    public static function static(array $colors)
    {
        return new self($colors, self::EFFECT_STATIC, 0, 0, 1, 0, 0, 0);
    }

    public static function _breathing()
    {
        return self::breathing(array("FF0000", "00FF00", "0000FF"), 1, .5, 1, .5, 0, 0, 255, 1);
    }

    public static function breathing(array $colors, float $off, float $fadein, float $on, float $fadeout, float $offset,
                                     int $min_val, int $max_value, int $color_cycles)
    {
        $args = [];
        $args["breathe_min_val"] = $min_val;
        $args["breathe_max_val"] = $max_value;
        $args["color_cycles"] = $color_cycles;
        return new self($colors, self::EFFECT_BREATHING, $off, $fadein, $on, $fadeout, 0, $offset, $args);
    }

    public static function _fading()
    {
        return self::fading(array("FF0000", "00FF00", "0000FF"), 0.5, 1, 0);
    }

    public static function fading(array $colors, float $fade, float $on, float $offset)
    {
        return new self($colors, self::EFFECT_FADING, 0, 0, $on, $fade, 0, $offset);
    }

    public static function _blinking()
    {
        return self::blinking(array("FF0000", "00FF00", "0000FF"), 1, 1, 0, 1);
    }

    public static function blinking(array $colors, float $off, float $on, float $offset, int $color_cycles)
    {
        $args = array("color_cycles" => $color_cycles);
        return new self($colors, self::EFFECT_BLINKING, $off, 0, $on, 0, 0, $offset, $args);
    }

    public static function _fillingFade()
    {
        return self::fillingFade(array("FF0000", "00FF00", "0000FF"), 1, 1, 0, 0, 1, 1, 1, 0, 0, 0, 1, 1);
    }

    public static function fillingFade(array $colors, float $on, float $fadeout, float $rotating, float $offset,
                                       bool $smooth, int $piece_count, int $color_count,
                                       int $dir1, int $dir2, int $return, int $fade_smooth, int $direction)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["fill_fade_return"] = $return;
        $args["fade_smooth"] = $fade_smooth;
        $args["fill_fade_color_count"] = $color_count;
        $args["fill_fade_piece_count"] = $piece_count;
        $args["fill_fade_direction1"] = $dir1;
        $args["fill_fade_direction2"] = $dir2;
        return new self($colors, self::EFFECT_FILLING_FADE, 0, 0, $on, $fadeout, $rotating, $offset, $args);
    }

    public static function _filling()
    {
        return self::filling(array("FF0000", "00FF00", "0000FF"), 1, 1, 1, 1, 0, 0, 1, 1, 1, 1, 0, 0, 0, 1);
    }

    public static function filling(array $colors, float $off, float $fadein, float $on, float $fadeout, float $rotating,
                                   float $offset, bool $smooth, int $piece_count, int $color_count, int $color_cycles,
                                   int $dir1, int $dir2, int $return, int $direction)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["fill_fade_return"] = $return;
        $args["fill_fade_color_count"] = $color_count;
        $args["fill_fade_piece_count"] = $piece_count;
        $args["fill_fade_direction1"] = $dir1;
        $args["fill_fade_direction2"] = $dir2;
        $args["color_cycles"] = $color_cycles;
        return new self($colors, self::EFFECT_FILLING, $off, $fadein, $on, $fadeout, $rotating, $offset, $args);
    }

    public static function _marquee()
    {
        return self::marquee(array("FF0000", "00FF00", "0000FF"), 1, 5, 2, 0, 1, 1);
    }

    public static function marquee(array $colors, int $fade, int $on, int $rotating, int $offset,
                                   bool $smooth, bool $direction)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        return new self($colors, self::EFFECT_MARQUEE, 0, 0, $on, $fade, $rotating, $offset, $args);
    }

    public static function _rainbow()
    {
        return self::rainbow(array(), 8, 0, 2, 0, 255);
    }

    public static function rainbow(array $colors, int $fade, int $offset, bool $smooth, bool $direction, int $brightness)
    {
        $args = array();
        $args["rainbow_brightness"] = $brightness;
        return new self($colors, self::EFFECT_RAINBOW, 0, 0, 0, $fade, 0, $offset, $args);
    }

    public static function _rainbowRotating()
    {
        return self::rainbowRotating(array(), 2, 0, 1, 1, 255, 1);
    }

    public static function rainbowRotating(array $colors, int $rotation, int $offset, bool $direction, int $mode,
                                           int $brightness, int $sources)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["rainbow_mode"] = $mode;
        $args["rainbow_brightness"] = $brightness;
        $args["rainbow_sources"] = $sources;
        return new self($colors, self::EFFECT_RAINBOW_ROTATING, 0, 0, 0, 0, $rotation, $offset, $args);
    }

    public static function _rotating()
    {
        return self::rotating(array("FF0000", "0000FF"), 4, 1, 2, 0, 1, 1, 1, 2, 3);
    }

    public static function rotating(array $colors, int $on, int $fade, int $rotating, int $offset, bool $smooth,
                                    bool $direction, int $color_count, int $element_count, int $led_count)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["rotating_color_count"] = $color_count;
        $args["rotating_element_count"] = $element_count;
        $args["rotating_led_count"] = $led_count;
        return new self($colors, self::EFFECT_ROTATING, 0, 0, $on, $fade, $rotating, $offset, $args);
    }

    public static function _pieces()
    {
        return self::pieces(array("FF0000", "0000FF"), 4, 1, 2, 0, 1, 1, 2, 4);
    }

    public static function pieces(array $colors, int $on, int $fade, int $rotating, int $offset, bool $smooth,
                                  bool $direction, int $color_count, int $piece_count)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["pieces_color_count"] = $color_count;
        $args["pieces_piece_count"] = $piece_count;
        return new self($colors, self::EFFECT_PIECES, 0, 0, $on, $fade, $rotating, $offset, $args);
    }

    public static function _spectrum()
    {
        return self::spectrum(array("FF0000", "0000FF"), 5, 1, 2, 0, 1, 0, 2);
    }

    public static function spectrum(array $colors, int $on, int $fade, int $rotating, int $offset, bool $direction, int $modes, int $color_count)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["spectrum_modes"] = $modes;
        $args["spectrum_color_count"] = $color_count;
        return new self($colors, self::EFFECT_SPECTRUM, 0, 0, $on, $fade, $rotating, $offset, $args);
    }

    public static function _two_halves()
    {
        return self::two_halves(array("#FF0000"), 1, .5, 1, .5, 0, true, 1, 1, 0, 1);
    }

    public static function two_halves(array $colors, float $off, float $fadein, float $on, float $fadeout, float $offset,
                                      bool $smooth, int $color_count, int $color_cycles, int $return, int $direction)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["two_halves_return"] = $return;
        $args["two_halves_color_count"] = $color_count;
        $args["color_cycles"] = $color_cycles;
        return new self($colors, self::EFFECT_TWO_HALVES_FADE, $off, $fadein, $on, $fadeout, 0, $offset, $args);
    }

    public static function _two_halves_fade()
    {
        return self::two_halves_fade(array("#FF0000"), 1, .5, 0, true, 1, 1, 0, 1, true);
    }

    public static function two_halves_fade(array $colors, float $on, float $fade, float $offset,
                                           bool $smooth, int $color_count, int $color_cycles, int $return, int $direction, bool $fade_smooth)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["fade_smooth"] = $fade_smooth;
        $args["two_halves_return"] = $return;
        $args["two_halves_color_count"] = $color_count;
        $args["color_cycles"] = $color_cycles;
        return new self($colors, self::EFFECT_TWO_HALVES_FADE, 0, 0, $on, $fade, 0, $offset, $args);
    }

    public static function _particles()
    {
        return self::particles(array("#FF0000"), 2, 1, 4, 0, self::DIRECTION_CW, true, 4);
    }

    public static function particles(array $colors, float $speed, float $delay, float $group_delay, float $offset, int $direction, bool $smooth, int $size)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["particles_size"] = $size;
        return new self($colors, self::EFFECT_PARTICLES, $speed, $delay, $group_delay, 0, 0, $offset, $args);
    }

    public static function fromJson(array $json)
    {
        $t = $json["times"];
        return new self($json["colors"], $json["effect"], $t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $json["args"]);
    }

    /**
     * @param int $effect
     * @return DigitalDevice
     */
    public static function defaultFromEffect(int $effect)
    {
        switch($effect)
        {
            case self::EFFECT_OFF:
                return self::_off();
            case self::EFFECT_STATIC:
                return self::_static();
            case self::EFFECT_BREATHING:
                return self::_breathing();
            case self::EFFECT_BLINKING:
                return self::_blinking();
            case self::EFFECT_FADING:
                return self::_fading();
            case self::EFFECT_RAINBOW:
                return self::_rainbow();
            case self::EFFECT_RAINBOW_ROTATING:
                return self::_rainbowRotating();
            case self::EFFECT_FILLING:
                return self::_filling();
            case self::EFFECT_FILLING_FADE:
                return self::_fillingFade();
            case self::EFFECT_MARQUEE:
                return self::_marquee();
            case self::EFFECT_ROTATING:
                return self::_rotating();
            case self::EFFECT_PIECES:
                return self::_pieces();
            case self::EFFECT_SPECTRUM:
                return self::_spectrum();
            case self::EFFECT_TWO_HALVES:
                return self::_two_halves();
            case self::EFFECT_TWO_HALVES_FADE:
                return self::_two_halves_fade();
            case self::EFFECT_PARTICLES:
                return self::_particles();
            default:
                throw new InvalidArgumentException("Unknown effect: " . $effect);
        }
    }

    public static function effects()
    {
        $effects = array();

        $effects[self::EFFECT_OFF] = "effect_off";
        $effects[self::EFFECT_STATIC] = "effect_static";
        $effects[self::EFFECT_BREATHING] = "effect_breathing";
        $effects[self::EFFECT_BLINKING] = "effect_blinking";
        $effects[self::EFFECT_FADING] = "effect_fading";
        $effects[self::EFFECT_RAINBOW] = "effect_rainbow";
        $effects[self::EFFECT_RAINBOW_ROTATING] = "effect_rainbow_rotating";
        $effects[self::EFFECT_FILLING] = "effect_filling";
        $effects[self::EFFECT_FILLING_FADE] = "effect_filling_fade";
        $effects[self::EFFECT_MARQUEE] = "effect_marquee";
        $effects[self::EFFECT_ROTATING] = "effect_rotating";
        $effects[self::EFFECT_PIECES] = "effect_pieces";
        $effects[self::EFFECT_SPECTRUM] = "effect_spectrum";
        $effects[self::EFFECT_TWO_HALVES] = "effect_two_halves";
        $effects[self::EFFECT_TWO_HALVES_FADE] = "effect_two_halves_fade";
        $effects[self::EFFECT_PARTICLES] = "effect_particles";
        /*$effects[self::EFFECT_SWEEP] = "effect_sweep";
        $effects[self::EFFECT_ANDROID_PB] = "effect_android_pb";
        $effects[self::EFFECT_TWO_HALVES] = "effect_two_halves";
        $effects[self::EFFECT_DOUBLE_FILL] = "effect_double_fill";
        $effects[self::EFFECT_HIGHS] = "effect_highs";
        $effects[self::EFFECT_SOURCES] = "effect_sources";
        $effects[self::EFFECT_DEMO] = "effect_demo";*/

        return $effects;
    }
}