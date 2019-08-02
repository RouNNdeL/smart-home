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

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2019-06-11
 * Time: 11:09
 */
class ActionEntry {
    const ACTION_TYPE_DELAY = 1;
    const ACTION_TYPE_BUTTON_PRESS = 2;
    const ACTION_TYPE_CHANNEL = 3;

    /** @var int */
    private $type;

    /** @var string */
    private $param;

    /**
     * ActionEntry constructor.
     * @param int $type
     * @param string $param
     */
    private function __construct(int $type, string $param) {
        $this->type = $type;
        $this->param = $param;
    }

    public function executeEntry(UserDeviceManager $manager, $physical_device_id) {
        switch($this->type) {
            case ActionEntry::ACTION_TYPE_BUTTON_PRESS:
                $ir_action = IrCode::byId($this->param);
                $physical = $manager->getPhysicalDeviceById($physical_device_id);
                if($physical === null)
                    throw new UnexpectedValueException("Invalid physical device id: $physical_device_id");
                if(!$physical instanceof IrRemote)
                    throw new UnexpectedValueException("Physical device should be of type IrRemote");

                $virtual = $physical->getVirtualDeviceById($ir_action->getDeviceId());
                if($virtual === null)
                    throw new UnexpectedValueException("Invalid action id");

                $physical->sendCode($ir_action);
                break;
            case ActionEntry::ACTION_TYPE_DELAY:
                usleep(intval($this->param) * 1000);
                break;
            case ActionEntry::ACTION_TYPE_CHANNEL:
                $digits = str_split($this->param);
                $physical = $manager->getPhysicalDeviceById($physical_device_id);
                if(!$physical instanceof IrRemote)
                    throw new UnexpectedValueException("Physical device should be of type IrRemote");

                foreach($digits as $digit) {
                    $code = "horizon_digit_" . $digit;
                    $ir_action = IrCode::byId($code);
                    $physical->sendCode($ir_action);
                    usleep(200000);
                }
                break;
            default:
                throw new UnexpectedValueException("Invalid action type " . $this->type);
        }
    }

    /**
     * @param int $action_id
     * @return ActionEntry[]
     */
    public static function getEntriesForActionId(int $action_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT type, param FROM remote_action_entries WHERE action_id = ? ORDER BY entry_order ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $action_id);
        $stmt->bind_result($type, $param);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            $arr[] = new ActionEntry($type, $param);
        }
        $stmt->close();
        return $arr;
    }

    /**
     * @param int $action_id
     * @return ActionEntry[][]
     */
    public static function getEntriesForUserId(int $user_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT type, param, action_id FROM remote_action_entries 
                  JOIN remote_actions ON remote_action_entries.action_id = remote_actions.id
                  WHERE user_id = ? ORDER BY action_id, entry_order ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->bind_result($type, $param, $action_id);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            if(!isset($arr[$action_id])) /* Might not be required */
                $arr[$action_id] = [];
            $arr[$action_id][] = new ActionEntry($type, $param);
        }
        $stmt->close();
        return $arr;
    }
}