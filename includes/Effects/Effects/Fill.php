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
use App\Effects\Arguments\DirectionArgument;
use App\Effects\Arguments\YesNoArgument;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-07-06
 * Time: 21:39
 */
class Fill extends Effect {

    const ARG_SMOOTH = "smooth";
    const ARG_RETURN = "fill_return";
    const ARG_ROTATE_DIR = "fill_rotation_direction";
    const ARG_COLOR_COUNT = "fill_color_count";
    const ARG_PIECE_COUNT = "fill_piece_count";
    const ARG_DIRECTION_1 = "fill_direction_1";
    const ARG_DIRECTION_2 = "fill_direction_2";

    /**
     * @param $name
     * @return Argument
     */
    public function getArgumentClass($name) {
        switch($name) {
            case Fill::ARG_ROTATE_DIR:
                return new DirectionArgument($name, $this->args[$name]);
            case Fill::ARG_SMOOTH:
            case Fill::ARG_RETURN:
                return new YesNoArgument($name, $this->args[$name]);
            default:
                return new Argument($name, $this->args[$name]);
        }
    }

    /**
     * @return int
     */
    public function getTimingsForEffect() {
        return (1 << Effect::TIME_OFF) | (1 << Effect::TIME_FADEIN) | (1 << Effect::TIME_ON) |
            (1 << Effect::TIME_FADEOUT) | (1 << Effect::TIME_DELAY) | (1 << Effect::TIME_ROTATION);
    }

    /**
     * @return array
     */
    public function packArgs() {
        $args = [];

        $args[0] = ($this->args[Fill::ARG_ROTATE_DIR] << 0) | ($this->args[Fill::ARG_SMOOTH] << 1) |
            ($this->args[Fill::ARG_RETURN] << 2);
        $args[1] = $this->args[Fill::ARG_COLOR_COUNT];
        $args[2] = $this->args[Fill::ARG_PIECE_COUNT];
        $args[3] = $this->args[Fill::ARG_DIRECTION_1];
        $args[4] = $this->args[Fill::ARG_DIRECTION_2];
        $args[5] = $this->args[Effect::ARG_COLOR_CYCLES];

        return $args;
    }

    public function unpackArgs(array $args) {
        $this->args[Fill::ARG_ROTATE_DIR] = $args[0] & (1 << 0) ? 1 : 0;
        $this->args[Fill::ARG_SMOOTH] = $args[0] & (1 << 1) ? 1 : 0;
        $this->args[Fill::ARG_RETURN] = $args[0] & (1 << 2) ? 1 : 0;
        $this->args[Fill::ARG_COLOR_COUNT] = $args[1];
        $this->args[Fill::ARG_PIECE_COUNT] = $args[2];
        $this->args[Fill::ARG_DIRECTION_1] = $args[3];
        $this->args[Fill::ARG_DIRECTION_2] = $args[4];
        $this->args[Effect::ARG_COLOR_CYCLES] = $args[5];
    }

    /**
     * @return int
     */
    public function avrEffect() {
        return Effect::AVR_EFFECT_FILL;
    }

    /**
     * @return int
     */
    public function getEffectId() {
        return Effect::EFFECT_FILL;
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
        if($this->args[Effect::ARG_COLOR_CYCLES] < 1)
            $this->args[Effect::ARG_COLOR_CYCLES] = 1;

        if(sizeof($this->colors) <= 0)
            $this->colors = [0xff0000];

        if($this->args[Fill::ARG_COLOR_COUNT] > sizeof($this->colors) || $this->args[Fill::ARG_COLOR_COUNT] <= 0)
            $this->args[Fill::ARG_COLOR_COUNT] = sizeof($this->colors);

        while(sizeof($this->colors) % $this->args[Fill::ARG_COLOR_COUNT] > 0 ||
            $this->args[Fill::ARG_COLOR_COUNT] < 1) {
            $this->args[Fill::ARG_COLOR_COUNT]++;
        }
        while($this->args[Fill::ARG_PIECE_COUNT] % $this->args[Fill::ARG_COLOR_COUNT] > 0) {
            $this->args[Fill::ARG_PIECE_COUNT]++;
        }
    }

    /**
     * @param int $id
     * @return Effect
     */
    public static function getDefault(int $id) {
        return new Fill($id, [0xff0000], [0, 2.5, 0, 2.5], [2, 1, 1, 0, 0]);
    }
}