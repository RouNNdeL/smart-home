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
 * Date: 2018-05-17
 * Time: 20:18
 */

require_once __DIR__ . "/BaseEffectDevice.php";
require_once __DIR__ . "/../effects/effects/Effect.php";

class DigitalEffectDevice extends BaseEffectDevice {

    public function getAvailableEffects() {
        return [Effect::EFFECT_OFF => "off",
            Effect::EFFECT_STATIC => "static",
            Effect::EFFECT_BREATHING => "breathe",
            Effect::EFFECT_BLINKING => "blink",
            Effect::EFFECT_FADING => "fade",
            Effect::EFFECT_PIECES => "pieces",
            Effect::EFFECT_SPECTRUM => "spectrum",
            Effect::EFFECT_SIMPLE_RAINBOW => "rainbow",
            Effect::EFFECT_ROTATING_RAINBOW => "rainbow_rotating",
            Effect::EFFECT_PARTICLES => "particles",
            Effect::EFFECT_FILL => "fill"
        ];
    }

    public function getDefaultEffect() {
        return Effect::EFFECT_STATIC;
    }
}