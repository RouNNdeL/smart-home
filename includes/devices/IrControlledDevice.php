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
 * Date: 2018-07-09
 * Time: 18:18
 */
abstract class IrControlledDevice extends VirtualDevice
{
    protected $protocol;

    public function __construct(string $device_id, string $device_name, $synonyms, string $device_type,
                                int $protocol)
    {
        parent::__construct($device_id, $device_name, $synonyms, $device_type, false, false);
        $this->protocol = $protocol;
    }


    public function getTraits()
    {
        return [VirtualDevice::DEVICE_TRAIT_ON_OFF];
    }

    public function getActionsDeviceType()
    {
        return VirtualDevice::DEVICE_TYPE_ACTIONS_SWITCH;
    }

    public function getAttributes()
    {
        return null;
    }

    /**
     * @param bool $online
     * @return array
     */
    public function getStateJson(bool $online = false)
    {
        return [
            "online" => $online,
        ];
    }

    /**
     * @return int
     */
    public function getProtocol(): int
    {
        return $this->protocol;
    }
}