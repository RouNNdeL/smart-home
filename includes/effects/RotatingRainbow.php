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
 * Date: 2018-07-04
 * Time: 20:37
 */
class RotatingRainbow extends Effect
{

    const ARG_DIRECTION = "direction";
    const ARG_BRIGHTNESS = "rainbow_brightness";
    const ARG_SOURCES = "rainbow_sources";
    const ARG_MODE = "rainbow_mode";

    /**
     * @return int
     */
    public function getTimingsForEffect()
    {
        return (1 << Effect::TIME_ROTATION) | (1 << Effect::TIME_DELAY);
    }

    /**
     * @return array
     */
    public function packArgs()
    {
        $args = [];

        $args[0] = ($this->args[RotatingRainbow::ARG_DIRECTION] << 0) | ($this->args[RotatingRainbow::ARG_MODE] << 2)
            & ~(1 << 3);
        $args[1] = $this->args[RotatingRainbow::ARG_BRIGHTNESS];
        $args[2] = $this->args[RotatingRainbow::ARG_SOURCES];
        $args[5] = 1;

        return $args;
    }

    public function unpackArgs(array $args)
    {
        $this->args[RotatingRainbow::ARG_DIRECTION] = $args[0] & (1 << 0) ? 1 : 0;
        $this->args[RotatingRainbow::ARG_MODE] = $args[0] & (1 << 2) ? 1 : 0;
        $this->args[RotatingRainbow::ARG_BRIGHTNESS] = $args[1];
        $this->args[RotatingRainbow::ARG_SOURCES] = $args[2];
    }

    /**
     * @return int
     */
    public function avrEffect()
    {
        return Effect::AVR_EFFECT_RAINBOW;
    }

    /**
     * @return int
     */
    public function getEffectId()
    {
        return Effect::EFFECT_ROTATING_RAINBOW;
    }

    /**
     * @return int
     */
    public function getMaxColors()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getMinColors()
    {
        return 0;
    }

    /**
     * Makes sure the submitted values aren't going to cause a crash by overwriting invalid user input
     * The updated_effect JSON filed then contains those values and replaces them in the user interface
     */
    public function overwriteValues()
    {
        if($this->args[RotatingRainbow::ARG_BRIGHTNESS] < 1)
            $this->args[RotatingRainbow::ARG_BRIGHTNESS] = 0xff;

        if($this->args[RotatingRainbow::ARG_SOURCES] < 1)
            $this->args[RotatingRainbow::ARG_SOURCES] = 1;

        $this->colors = [0]; /* Required in order to send color count greater then 0 */
    }

    /**
     * @param int $id
     * @return Effect
     */
    public static function getDefault(int $id)
    {
        return new RotatingRainbow($id, [], [0, 0, 0, 0, 5], [0, 0xff, 1]);
    }

    /**
     * @param $name
     * @return string
     */
    public function getArgumentClass($name)
    {
        switch($name)
        {
            case RotatingRainbow::ARG_DIRECTION:
                return new DirectionArgument($name, $this->args[$name]);
            case RotatingRainbow::ARG_MODE:
                return new BlendModeArgument($name, $this->args[$name]);
            default:
                return new Argument($name, $this->args[$name]);
        }
    }
}