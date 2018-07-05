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
 * Time: 15:24
 */
class Spectrum extends Effect
{
    const TIME_FADE = 3;
    const ARG_DIRECTION = "direction";
    const ARG_COLOR_COUNT = "spectrum_color_count";
    const ARG_BLEND_MODE = "spectrum_blend_mode";

    /**
     * @return int
     */
    public function getTimingsForEffect()
    {
        return (1 << Effect::TIME_ON) | (1 << Spectrum::TIME_FADE) |
            (1 << Effect::TIME_ROTATION) | (1 << Effect::TIME_DELAY);
    }

    protected function getTimingStrings()
    {
        $strings = parent::getTimingStrings();
        $strings[Fade::TIME_FADE] = "fade";
        return $strings;
    }

    /**
     * @return array
     */
    public function packArgs()
    {
        $args = [];
        $args[0] = $this->args[Spectrum::ARG_DIRECTION] << 0;
        $args[1] = $this->args[Spectrum::ARG_COLOR_COUNT];
        $args[2] = $this->args[Spectrum::ARG_BLEND_MODE];
        $args[5] = 1;
        return $args;
    }

    public function unpackArgs(array $args)
    {
        $this->args[Spectrum::ARG_DIRECTION] = $args[0] & (1 << 0) ? 1 : 0;
        $this->args[Spectrum::ARG_COLOR_COUNT] = $args[1];
        $this->args[Spectrum::ARG_BLEND_MODE] = $args[2];
    }

    /**
     * @return int
     */
    public function avrEffect()
    {
        return Effect::AVR_EFFECT_SPECTRUM;
    }

    /**
     * @return int
     */
    public function getEffectId()
    {
        return Effect::EFFECT_SPECTRUM;
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
        return 2;
    }

    /**
     * Makes sure the submitted values aren't going to cause a crash by overwriting invalid user input
     */
    public function overwriteValues()
    {

    }

    /**
     * @param int $id
     * @return Effect
     */
    public static function getDefault(int $id)
    {
        return new Spectrum($id, [0xff0000, 0x0000ff], [0, 0, 4, 1], [2, 2, 0]);
    }
}