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
 * Date: 2018-07-06
 * Time: 12:33
 */
class Particles extends Effect
{
    const TIME_PARTICLE_SPEED = 0;
    const TIME_PARTICLE_DELAY = 1;

    const ARG_SMOOTH = "smooth";
    const ARG_DIRECTION = "direction";
    const ARG_PARTICLE_SIZE = "particles_size";

    /**
     * @return int
     */
    public function getTimingsForEffect()
    {
        return (1 << Particles::TIME_PARTICLE_SPEED) | (1 << Particles::TIME_PARTICLE_DELAY) | (1 << Particles::TIME_DELAY);
    }

    /**
     * @return array
     */
    public function packArgs()
    {
        $args = [];
        $args[0] = ($this->args[Particles::ARG_DIRECTION] << 0) | ($this->args[Particles::ARG_SMOOTH] << 1);
        $args[1] = $this->args[Particles::ARG_PARTICLE_SIZE];
        $args[5] = $this->args[Effect::ARG_COLOR_CYCLES];
        return $args;
    }

    public function unpackArgs(array $args)
    {
        $this->args[Particles::ARG_DIRECTION] = $args[0] & (1 << 0) ? 1 : 0;
        $this->args[Particles::ARG_SMOOTH] = $args[0] & (1 << 1) ? 1 : 0;
        $this->args[Particles::ARG_PARTICLE_SIZE] = $args[1];
        $this->args[Effect::ARG_COLOR_CYCLES] = $args[5];
    }

    protected function getTimingStrings()
    {
        $strings = parent::getTimingStrings();

        $strings[Particles::TIME_PARTICLE_SPEED] = "particles_speed";
        $strings[Particles::TIME_PARTICLE_DELAY] = "particles_delay";

        return $strings;
    }

    /**
     * @return int
     */
    public function avrEffect()
    {
        return Effect::AVR_EFFECT_PARTICLES;
    }

    /**
     * @return int
     */
    public function getEffectId()
    {
        return Effect::EFFECT_PARTICLES;
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

    /**
     * Makes sure the submitted values aren't going to cause a crash by overwriting invalid user input
     * The updated_effect JSON filed then contains those values and replaces them in the user interface
     */
    public function overwriteValues()
    {
        if($this->args[Particles::ARG_PARTICLE_SIZE] < 1)
            $this->args[Particles::ARG_PARTICLE_SIZE] = 4;

        if($this->args[Effect::ARG_COLOR_CYCLES] < 1)
            $this->args[Effect::ARG_COLOR_CYCLES] = 1;
    }

    /**
     * @param int $id
     * @return Effect
     */
    public static function getDefault(int $id)
    {
        return new Particles($id, [0xff0000], [2, 2], [3, 4]);
    }

    /**
     * @param $name
     * @return string
     */
    public function getArgumentClass($name)
    {

        switch($name)
        {
            case Particles::ARG_DIRECTION:
                return new DirectionArgument($name, $this->args[$name]);
            case Particles::ARG_SMOOTH:
                return new YesNoArgument($name, $this->args[$name]);
            default:
                return new Argument($name, $this->args[$name]);
        }
    }
}