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
 * Date: 2018-07-02
 * Time: 16:45
 */

require_once __DIR__ . "/Effect.php";

class Fade extends Effect
{
    const TIME_FADE = 3;

    /**
     * @return int
     */
    public function getTimingsForEffect()
    {
        return (1 << Fade::TIME_FADE) | (1 << Effect::TIME_ON) | (1 << Effect::TIME_DELAY);
    }

    /**
     * @return array
     */
    public function packArgs()
    {
        return [2 => 0xff, 5 => 1];
    }

    protected function getTimingStrings()
    {
        $strings = parent::getTimingStrings();
        $strings[Fade::TIME_FADE] = "fade";
        return $strings;
    }

    public function unpackArgs(array $args)
    {

    }

    /**
     * @return int
     */
    public function avrEffect()
    {
        return Effect::AVR_EFFECT_FADE;
    }

    /**
     * @return int
     */
    public function getEffectId()
    {
        return Effect::EFFECT_FADING;
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
     * The updated_effect JSON filed then contains those values and replaces them in the user interface
     */
    public function overwriteValues()
    {
        $this->timings[Effect::TIME_OFF] = 0;
        $this->timings[Effect::TIME_FADEIN] = 0;
        $this->timings[Effect::TIME_ROTATION] = 0;
        if(sizeof($this->colors) < 2)
            $this->colors = [0xff0000, 0x0000ff];

    }

    /**
     * @param int $id
     * @param string $device_id
     * @return Effect
     */
    public static function getDefault(int $id)
    {
        return new Fade($id, [0xff0000, 0x0000ff], [0, 0, 4, 1, 0, 0]);
    }
}