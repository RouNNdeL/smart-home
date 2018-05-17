<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 20:18
 */

require_once __DIR__."/RgbEffectDevice.php";
require_once __DIR__."/../effects/Effect.php";

class SimpleEffectDevice extends RgbEffectDevice
{

    public function getAvailableEffects()
    {
        return [Effect::EFFECT_OFF, Effect::EFFECT_STATIC, Effect::EFFECT_BREATHING];
    }

    public function colorLimit()
    {
        return 16;
    }
}