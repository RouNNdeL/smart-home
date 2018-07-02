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
 * Date: 2018-06-11
 * Time: 17:47
 */
class Off extends Effect
{

    /**
     * @return int
     */
    public function getTimingsForEffect()
    {
        return 0;
    }

    /**
     * @return array
     */
    public function argsToArray()
    {
        return [1 => 0, 5 => 1];
    }

    /**
     * @return array
     */
    public function argList()
    {
        return [];
    }

    /**
     * @return int
     */
    public function avrEffect()
    {
        return Effect::AVR_EFFECT_BREATHE;
    }

    /**
     * @return int
     */
    public function getEffectId()
    {
        return Effect::EFFECT_OFF;
    }

    /**
     * @param int $id
     * @param string $device_id
     * @return Effect
     */
    public static function getDefault(int $id, string $device_id)
    {
        return new Off($id, $device_id, [], [1, 0, 0, 0, 0, 0]);
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

    public function overwriteValues()
    {
        $this->timings[Effect::TIME_OFF] = 1;
        $this->timings[Effect::TIME_ROTATION] = 0;
    }
}