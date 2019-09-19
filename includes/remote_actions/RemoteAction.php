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
 * Time: 13:21
 */

require_once __DIR__ . "/ActionEntry.php";

class RemoteAction {
    const ID_PREFIX = "remote_action_";

    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $physical_device_id;

    /** @var int */
    private $user_id;

    /** @var string[] */
    private $synonyms;

    /** @var ActionEntry[] */
    private $entries;

    /** @var int */
    private $default_delay;

    /** @var int */
    private $deactivate_action_id;

    /** @var bool */
    private $is_deactivate;

    /**
     * RemoteAction constructor.
     * @param string $name
     * @param string[] $synonyms
     * @param ActionEntry[] $entries
     * @param int $default_delay
     */
    private function __construct(int $id, string $name, string $physical_device_id, int $user_id, array $synonyms,
                                 array $entries, int $default_delay, bool $is_deactivate, $deactivate_action_id
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->physical_device_id = $physical_device_id;
        $this->user_id = $user_id;
        $this->synonyms = $synonyms;
        $this->entries = $entries;
        $this->default_delay = $default_delay;
        $this->is_deactivate = $is_deactivate;
        $this->deactivate_action_id = $deactivate_action_id;
    }


    public function executeAction(UserDeviceManager $manager) {
        foreach($this->entries as $entry) {
            $entry->executeEntry($manager, $this->physical_device_id);
            usleep($this->default_delay * 1000);
        }
    }

    public function getSyncJson() {
        return [
            "id" => $this->getPrefixedId(),
            "name" => ["name" => $this->name, "nicknames" => array_merge([$this->name], $this->synonyms)],
            "type" => VirtualDevice::DEVICE_TYPE_ACTIONS_SCENE,
            "traits" => [VirtualDevice::DEVICE_TRAIT_SCENE],
            "willReportState" => false,
            "attributes" => [
                VirtualDevice::DEVICE_ATTRIBUTE_SCENE_REVERSIBLE => $this->deactivate_action_id !== null
            ]
        ];
    }

    public function getPrefixedId() {
        return RemoteAction::ID_PREFIX . $this->id;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPhysicalDeviceId(): string {
        return $this->physical_device_id;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isDeactivate(): bool {
        return $this->is_deactivate;
    }

    /**
     * @return int
     */
    public function getDeactivateActionId(): int {
        return $this->deactivate_action_id;
    }

    /**
     * @param int $id
     * @return RemoteAction|null
     */
    public static function byId(int $id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT name, physcial_device_id, user_id, synonyms, default_delay, is_deactivate, deactivate_action_id
                FROM remote_actions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->bind_result($name, $physical_device_id, $user_id,
            $synonyms, $default_delay, $is_deactivate, $deactivate_action_id);
        $stmt->execute();
        if($stmt->fetch()) {
            $stmt->close();
            $entries = ActionEntry::getEntriesForActionId($id);
            return new RemoteAction($id, $name, $physical_device_id, $user_id, explode(",", $synonyms),
                $entries, $default_delay, $is_deactivate, $deactivate_action_id);
        }
        $stmt->close();
        return null;
    }

    /**
     * @param int $id
     * @return RemoteAction[]
     */
    public static function forUserId(int $user_id, bool $is_deactivate = null) {
        if($is_deactivate !== null) {
            if($is_deactivate) {
                $deactivate_sql = "AND is_deactivate = 1";
            } else {
                $deactivate_sql = "AND is_deactivate = 0";
            }
        } else {
            $deactivate_sql = "";
        }
        $entries = ActionEntry::getEntriesForUserId($user_id);

        $conn = DbUtils::getConnection();
        $sql = "SELECT id, name, physcial_device_id, synonyms, default_delay, is_deactivate, deactivate_action_id
                FROM remote_actions WHERE user_id = ? $deactivate_sql";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->bind_result($id, $name, $physical_device_id,
            $synonyms, $default_delay, $is_deactivate, $deactivate_action_id);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            $arr[] = new RemoteAction($id, $name, $physical_device_id, $user_id, explode(",", $synonyms),
                $entries[$id], $default_delay, $is_deactivate, $deactivate_action_id);
        }
        $stmt->close();
        return $arr;
    }
}