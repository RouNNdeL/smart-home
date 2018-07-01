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
 * Time: 13:46
 */

class Breathe extends Effect
{
    const ARG_MIN_VALUE = "breathe_min_val";
    const ARG_MAX_VALUE = "breathe_max_val";

    public function getTimingsForEffect()
    {
        return (1 << Effect::TIME_OFF) | (1 << Effect::TIME_FADEIN) | (1 << Effect::TIME_ON) |
            (1 << Effect::TIME_FADEOUT) | (1 << Effect::TIME_DELAY);
    }

    public function argsToArray()
    {
        $array = [];
        $array[1] = $this->args[Breathe::ARG_MIN_VALUE];
        $array[2] = $this->args[Breathe::ARG_MAX_VALUE];
        $array[5] = $this->args[Breathe::ARG_COLOR_CYCLES];
        return $array;
    }

    public function argList()
    {
        return [1 => Breathe::ARG_MIN_VALUE, 2 => Breathe::ARG_MAX_VALUE, 5 => Effect::ARG_COLOR_CYCLES];
    }

    public function avrEffect()
    {
        return Effect::AVR_EFFECT_BREATHE;
    }

    public function getEffectId()
    {
        return Effect::EFFECT_BREATHING;
    }

    public static function getDefault(int $id, string $device_id)
    {
        return new Breathe($id, $device_id, [0xff0000, 0x00ff00, 0x0000ff], [0, 1, 0, 1, 0, 0], [1, 0, 255]);
    }

    /**
     * @return int
     */
    public function getMaxColors()
    {
        return Effect::COLOR_COUNT_UNLIMITED;
    }

    /**
     * @return int
     */
    public function getMinColors()
    {
        return 1;
    }
}