<?php
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
        return 0b111101;
    }

    public function argsToArray()
    {
        $array = [];
        $array[1] = $this->args[self::ARG_MIN_VALUE];
        $array[2] = $this->args[self::ARG_MAX_VALUE];
        return $array;
    }

    public function argList()
    {
        return [self::ARG_MIN_VALUE, self::ARG_MAX_VALUE, Effect::ARG_COLOR_CYCLES];
    }

    public function avrEffect()
    {
        return Effect::AVR_EFFECT_BREATHE;
    }

    public function getEffectId()
    {
        return self::EFFECT_BREATHING;
    }

    public static function getDefault()
    {
        return new Breathe([0xff0000, 0x00ff00, 0x0000ff], 0, 1, 0, 1, 0, 0, [0, 255, 1]);
    }
}