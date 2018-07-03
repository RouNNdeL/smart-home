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
 * Date: 2018-07-03
 * Time: 23:55
 */
class Pieces extends Effect
{
    const TIME_FADE = 3;
    const ARG_SMOOTH = "smooth";
    const ARG_DIRECTION = "direction";
    const ARG_PIECE_COUNT = "pieces_piece_count";
    const ARG_COLOR_COUNT = "pieces_color_count";

    /**
     * @return int
     */
    public function getTimingsForEffect()
    {
        return (1 << Effect::TIME_ON) | (1 << Pieces::TIME_FADE) | (1 << Effect::TIME_ROTATION) | (1 << Effect::TIME_DELAY);
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
        $args[0] = ($this->args[Pieces::ARG_DIRECTION] ? 1 : 0) << 0 | ($this->args[Pieces::ARG_SMOOTH] ? 1 : 0) << 1;
        $args[1] = $this->args[Pieces::ARG_COLOR_COUNT];
        $args[2] = $this->args[Pieces::ARG_PIECE_COUNT];
        $args[5] = 1;
        return $args;
    }

    public function unpackArgs(array $args)
    {
        $this->args[Pieces::ARG_DIRECTION] = $args[0] & (1 << 0) ? true : false;
        $this->args[Pieces::ARG_SMOOTH] = $args[0] & (1 << 1) ? true : false;
        $this->args[Pieces::ARG_COLOR_COUNT] = $args[1];
        $this->args[Pieces::ARG_PIECE_COUNT] = $args[2];
    }

    /**
     * @return int
     */
    public function avrEffect()
    {
        return Effect::AVR_EFFECT_PIECES;
    }

    /**
     * @return int
     */
    public function getEffectId()
    {
        return Effect::EFFECT_PIECES;
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

    public function overwriteValues()
    {
        if($this->timings[Effect::TIME_ON] === 0 && $this->timings[Pieces::TIME_FADE] === 0)
            $this->timings[Effect::TIME_ON] = 1;
    }

    /**
     * @param int $id
     * @param string $device_id
     * @return Effect
     */
    public static function getDefault(int $id, string $device_id)
    {
        return new Pieces($id, $device_id, [0xff0000, 0x0000ff], [0, 0, 4, 1, 5], [2, 2, 2, 0, 0, 1]);
    }
}