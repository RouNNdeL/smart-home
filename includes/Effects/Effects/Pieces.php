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
 * Date: 2018-07-03
 * Time: 23:55
 */
class Pieces extends Effect {
    const TIME_FADE = 3;
    const ARG_SMOOTH = "smooth";
    const ARG_DIRECTION = "direction";
    const ARG_PIECE_COUNT = "pieces_piece_count";
    const ARG_COLOR_COUNT = "pieces_color_count";

    /**
     * @return int
     */
    public function getTimingsForEffect() {
        return (1 << Effect::TIME_ON) | (1 << Pieces::TIME_FADE) | (1 << Effect::TIME_ROTATION) | (1 << Effect::TIME_DELAY);
    }

    protected function getTimingStrings() {
        $strings = parent::getTimingStrings();
        $strings[Fade::TIME_FADE] = "fade";
        return $strings;
    }

    /**
     * @return array
     */
    public function packArgs() {
        $args = [];
        $args[0] = ($this->args[Pieces::ARG_DIRECTION] << 0) | ($this->args[Pieces::ARG_SMOOTH] << 1);
        $args[1] = $this->args[Pieces::ARG_COLOR_COUNT];
        $args[2] = $this->args[Pieces::ARG_PIECE_COUNT];
        $args[5] = 1;
        return $args;
    }

    public function unpackArgs(array $args) {
        $this->args[Pieces::ARG_DIRECTION] = $args[0] & (1 << 0) ? 1 : 0;
        $this->args[Pieces::ARG_SMOOTH] = $args[0] & (1 << 1) ? 1 : 0;
        $this->args[Pieces::ARG_COLOR_COUNT] = $args[1];
        $this->args[Pieces::ARG_PIECE_COUNT] = $args[2];
    }

    /**
     * @return int
     */
    public function avrEffect() {
        return Effect::AVR_EFFECT_PIECES;
    }

    /**
     * @return int
     */
    public function getEffectId() {
        return Effect::EFFECT_PIECES;
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
        return 2;
    }

    /**
     * Makes sure the submitted values aren't going to cause a crash by overwriting invalid user input
     * The updated_effect JSON filed then contains those values and replaces them in the user interface
     */
    public function overwriteValues() {
        if(sizeof($this->colors) < 2)
            $this->colors = [0xff0000, 0x0000ff];
        if($this->timings[Effect::TIME_ON] === 0 && $this->timings[Pieces::TIME_FADE] === 0)
            $this->timings[Effect::TIME_ON] = 0x10;
        if($this->args[Pieces::ARG_COLOR_COUNT] > sizeof($this->colors) || $this->args[Pieces::ARG_COLOR_COUNT] <= 0)
            $this->args[Pieces::ARG_COLOR_COUNT] = sizeof($this->colors);
        while(sizeof($this->colors) % $this->args[Pieces::ARG_COLOR_COUNT] > 0 ||
            $this->args[Pieces::ARG_COLOR_COUNT] < 2) {
            $this->args[Pieces::ARG_COLOR_COUNT]++;
        }
        while($this->args[Pieces::ARG_PIECE_COUNT] % $this->args[Pieces::ARG_COLOR_COUNT] > 0) {
            $this->args[Pieces::ARG_PIECE_COUNT]++;
        }
    }

    /**
     * @param $name
     * @return string
     */
    public function getArgumentClass($name) {
        switch($name) {
            case Pieces::ARG_DIRECTION:
                return new DirectionArgument($name, $this->args[$name]);
            case Pieces::ARG_SMOOTH:
                return new YesNoArgument($name, $this->args[$name]);
            default:
                return new Argument($name, $this->args[$name]);
        }
    }

    /**
     * @param int $id
     * @param string $device_id
     * @return Effect
     */
    public static function getDefault(int $id) {
        return new Pieces($id, [0xff0000, 0x0000ff], [0, 0, 4, 1, 5], [3, 2, 2, 0, 0, 1]);
    }
}