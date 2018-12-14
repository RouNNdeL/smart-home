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
 * Date: 2018-05-14
 * Time: 17:59
 */

require_once __DIR__ . "/EspWifiLedController.php";
require_once __DIR__ . "/EspWiFiLamp.php";
require_once __DIR__ . "/IrRemote.php";
require_once __DIR__ . "/PcLedController.php";
require_once __DIR__ . "/../database/HomeUser.php";
require_once __DIR__ . "/../Utils.php";

abstract class PhysicalDevice {
    /** @var VirtualDevice[] */
    protected $virtual_devices;

    /** @var string */
    private $id;

    /** @var string */
    private $display_name;

    /** @var int */
    private $owner_id;

    protected $hostname;

    protected $port;

    protected $online = null;

    /** @var array */
    private $scopes;

    /**
     * PhysicalDevice constructor.
     * @param string $id
     * @param int $owner_id
     * @param string $display_name
     * @param VirtualDevice[] $virtual_devices
     */
    protected function __construct(string $id, int $owner_id, string $display_name, string $hostname, int $port, array $virtual_devices, array $scopes) {
        $this->id = $id;
        $this->owner_id = $owner_id;
        $this->display_name = $display_name;
        $this->hostname = $hostname;
        $this->port = $port;
        $this->virtual_devices = $virtual_devices;
        $this->scopes = $scopes;
    }

    /**
     * @return bool
     */
    public function isOnline() {
        if($this->online !== null)
            return $this->online;
        $waitTimeoutInSeconds = .2;
        $fp = @fsockopen($this->hostname, $this->port, $errCode, $errStr, $waitTimeoutInSeconds);
        $this->online = $fp !== false;
        DeviceDbHelper::setOnline(DbUtils::getConnection(), $this->getId(), $this->online);
        if($this->online)
            fclose($fp);
        return $this->online;
    }

    /**
     * @return bool - whether any changes were made to the Database
     */
    public function save() {
        $changed = false;
        foreach($this->virtual_devices as $virtual_device) {
            $d_changed = $virtual_device->toDatabase();
            $changed = $changed || $d_changed;
        }
        return $changed;
    }

    public abstract function sendData(bool $quick);

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @return PhysicalDevice
     */
    public static abstract function load(string $device_id, int $owner_id, string $display_name,
                                         string $hostname, int $port, array $scopes
    );

    public abstract function reboot();

    /**
     * @param array $action
     * @param string $request_id
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    public function handleAssistantAction(array $action) {
        $ids = [];
        foreach($action["commands"] as $command) {
            foreach($command["devices"] as $d) {
                $device = $this->getVirtualDeviceById($d["id"]);
                if($device !== null) {
                    $ids[] = $device->getDeviceId();
                    foreach($command["execution"] as $item) {
                        $device->handleAssistantAction($item);
                    }
                }
            }
        }

        if($this->save()) {
            $this->sendData(false);
        }

        return ["status" => ($this->isOnline() ? "SUCCESS" : "OFFLINE"), "ids" => $ids];
    }

    /**
     * @param string $id
     * @return &VirtualDevice|null
     */
    public function getVirtualDeviceById(string $id
    ) {
        foreach($this->virtual_devices as &$virtual_device) {
            if($virtual_device->getDeviceId() === $id)
                return $virtual_device;
        }
        return null;
    }

    /**
     * @param string $id
     * @return int
     */
    public function getVirtualDeviceIndexById(string $id
    ) {
        foreach($this->virtual_devices as $i => $virtual_device) {
            if($virtual_device->getDeviceId() === $id)
                return $i;
        }
        return -1;
    }

    /**
     * @return VirtualDevice[]
     */
    public function getVirtualDevices(): array {
        return $this->virtual_devices;
    }

    public function getDeviceNavbarHtml() {
        $html = "";

        foreach($this->virtual_devices as $i => $virtual_device) {
            $active = $i ? "" : "active";
            $name = $virtual_device->getDeviceName();
            $sanitized_name = Utils::sanitizeString($name);
            $html .= "<li class=\"nav-item\" role=\"presentation\"" .
                "><a id=\"device-link-$sanitized_name\" href=\"#$sanitized_name\" class=\"nav-link device-link $active\">"
                . $name . "</a></li>";
        }

        return $html;
    }

    public function getRowHtml(int $user_id
    ) {
        $id = urlencode($this->id);
        $display_name = urlencode($this->display_name);

        if(sizeof($this->virtual_devices) > 1) {
            $devices = "- " . Utils::getString("device_devices") . ": ";
            foreach($this->virtual_devices as $i => $device) {
                if($i > 0) $devices .= ", ";
                $devices .= $device->getDeviceName();
            }
            $devices .= "<br>";
        } else {
            $devices = "";
        }

        $owner = HomeUser::queryUserById(DbUtils::getConnection(), $this->owner_id)->formatName();
        if($user_id === $this->owner_id) {
            $owner = Utils::getString("device_owner_you") . " (" . $owner . ")";
        }
        $name = $this->getNameWithState();

        return <<<HTML
<a href="/device/$display_name/$id" class="list-group-item list-group-item-action device-list-item" data-device-id="$id">
        <div class="row">
            <div class="col">
                <h5 class="card-title">$name</h5>
                <p class="card-text">
                    $devices
                    - Owner: <b>$owner</b><br>
                </p>
            </div>
        </div>
    </a>
HTML;

    }

    public static function fromDatabaseRow(array $row
    ) {
        if(!class_exists($row["device_driver"]) || !is_subclass_of($row["device_driver"], PhysicalDevice::class)) {
            throw new InvalidArgumentException("$row[device_driver] is not a valid PhysicalDevice class name");
        }

        $scopes = isset($row["scope"]) ? explode(" ", $row["scope"]) : [];
        return $row["device_driver"]::load($row["id"], $row["owner_id"],
            $row["display_name"], $row["hostname"], $row["port"], $scopes);
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return $this->display_name;
    }

    public function getNameWithState() {
        $class = $this->isOnline() ? "invisible" : "";
        return trim("$this->display_name <span class=\"device-offline-text $class\">(" . Utils::getString("device_status_offline") . ")</span>");
    }

    public function hasScope(string $scope) {
        return in_array($scope, $this->scopes);
    }

    /**
     * @return int
     */
    public function getOwnerId(): int {
        return $this->owner_id;
    }
}