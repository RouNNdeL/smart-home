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
 * Date: 2018-06-07
 * Time: 18:14
 */
class LampSimple extends VirtualDevice
{
    /** @var bool */
    protected $on;

    /**
     * SimpleRgbDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param array $synonyms
     * @param bool $home_actions
     * @param bool $will_report_state
     * @param bool $on
     */
    public function __construct(string $device_id, string $device_name, array $synonyms, bool $home_actions, bool $will_report_state, bool $on = true)
    {
        parent::__construct($device_id, $device_name, $synonyms, VirtualDevice::DEVICE_TYPE_LAMP, $home_actions, $will_report_state);
        $this->on = $on;
    }


    /**
     * @param array $command
     */
    public
    function handleAssistantAction($command
    )
    {
        switch($command["command"])
        {
            case VirtualDevice::DEVICE_COMMAND_ON_OFF:
                $this->on = $command["params"]["on"];
                break;
        }
    }

    /**
     * @param bool $online
     * @return array
     */
    public
    function getStateJson(bool $online = false
    )
    {
        return [
            "on" => $this->on,
            "online" => $online,
        ];
    }

    public
    function toDatabase()
    {
        $conn = DbUtils::getConnection();
        $sql = "UPDATE devices_virtual SET 
                  state = $this->on WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $this->device_id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @return string
     */
    public
    function toHTML()
    {
        // TODO: Implement toHTML() method.
        return "";
    }

    public
    function getTraits()
    {
        return [self::DEVICE_TRAIT_ON_OFF];
    }

    public
    function getActionsDeviceType()
    {
        return self::DEVICE_TYPE_ACTIONS_LIGHT;
    }

    public
    function getAttributes()
    {
        return [];
    }

    /**
     * @return bool
     */
    public
    function isOn(): bool
    {
        return $this->on;
    }

    /**
     * @param bool $on
     */
    public
    function setOn(bool $on
    )
    {
        $this->on = $on;
    }
}