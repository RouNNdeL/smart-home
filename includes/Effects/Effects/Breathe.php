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

namespace App\Effects\Effects;

use App\Effects\Arguments\Argument;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-16
 * Time: 13:46
 */
class Breathe extends Effect {
    const ARG_MIN_VALUE = "breathe_min_val";
    const ARG_MAX_VALUE = "breathe_max_val";

    public function getTimingsForEffect() {
        return (1 << Effect::TIME_OFF) | (1 << Effect::TIME_FADEIN) | (1 << Effect::TIME_ON) |
            (1 << Effect::TIME_FADEOUT) | (1 << Effect::TIME_DELAY);
    }

    public function packArgs() {
        $array = [];
        $array[1] = $this->args[Breathe::ARG_MIN_VALUE];
        $array[2] = $this->args[Breathe::ARG_MAX_VALUE];
        $array[5] = $this->args[Effect::ARG_COLOR_CYCLES];
        return $array;
    }

    public function unpackArgs(array $args) {
        $this->args[Breathe::ARG_MIN_VALUE] = $args[1];
        $this->args[Breathe::ARG_MAX_VALUE] = $args[2];
        $this->args[Effect::ARG_COLOR_CYCLES] = $args[5];
    }

    public function avrEffect() {
        return Effect::AVR_EFFECT_BREATHE;
    }

    public function getEffectId() {
        return Effect::EFFECT_BREATHING;
    }

    /**
     * @return int
     */
    public function getMaxColors() {
        return Effect::COLOR_COUNT_UNLIMITED;
    }

    /**
     * @return int
     */
    public function getMinColors() {
        return 1;
    }

    /**
     * Makes sure the submitted values aren't going to cause a crash by overwriting invalid user input
     * The updated_effect JSON filed then contains those values and replaces them in the user interface
     */
    public function overwriteValues() {
        $this->timings[Effect::TIME_ROTATION] = 0;

        if($this->args[Breathe::ARG_MAX_VALUE] < $this->args[Breathe::ARG_MIN_VALUE] ||
            $this->args[Breathe::ARG_MAX_VALUE] == 0) {
            $this->args[Breathe::ARG_MAX_VALUE] = 0xff;
        }

        if($this->args[Effect::ARG_COLOR_CYCLES] < 1)
            $this->args[Effect::ARG_COLOR_CYCLES] = 1;
    }

    /**
     * @param $name
     * @return string
     */
    public function getArgumentClass($name) {
        return new Argument($name, $this->args[$name]);
    }

    public static function getDefault(int $id) {
        return new Breathe($id, [0xff0000, 0x00ff00, 0x0000ff], [0, 1, 0, 1, 0, 0], [1, 0, 255, 0, 0, 1]);
    }
}