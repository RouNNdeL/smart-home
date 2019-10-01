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

namespace App\Devices;

use App\Database\{DbUtils, DeviceDbHelper, DeviceModManager, HomeUser};
use App\Utils;
use InvalidArgumentException;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 17:59
 */
abstract class PhysicalDevice {
    /** @var VirtualDevice[] */
    protected $virtual_devices;
    /** @var string */
    protected $display_name;
    protected $hostname;
    protected $port;
    protected $online = null;
    /** @var string */
    private $id;
    /** @var int */
    private $owner_id;
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

    public abstract function reboot();

    /**
     * @param array $action
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


        if($this->save("google_assistant_action")) {
            $status = $this->sendData(false) ? "SUCCESS" : "ERROR:deviceTurnedOff";
        } else {
            $status = "ERROR:alreadyInState";
        }

        return ["status" => $status, "ids" => $ids];
    }

    public function handleSmartThingsCommand(array $payload) {
        $response = [];
        foreach($payload["devices"] as $device) {
            $id = $device["externalDeviceId"];
            $virtualDevice = $this->getVirtualDeviceById($id);
            if($virtualDevice !== null) {
                $virtualDevice->processSmartThingsCommand($device["commands"]);
                $state = $virtualDevice->getSmartThingsState($this->isOnline());
                if($state !== null) {
                    $response[] = $devices_payload[] = [
                        "externalDeviceId" => $id,
                        "deviceCookie" => [],
                        "states" => $state
                    ];
                }
            }
        }

        if($this->save("samsung_smart_things_command")){
            $this->sendData(false);
        }
        return $response;
    }

    /**
     * @param string $id
     * @return VirtualDevice|null
     */
    public function getVirtualDeviceById(string $id) {
        foreach($this->virtual_devices as &$virtual_device) {
            if($virtual_device->getDeviceId() === $id)
                return $virtual_device;
        }

        return null;
    }

    /**
     * @param string $issuer_id
     * @return bool - whether any changes were made to the Database
     */
    public function save(string $issuer_id) {
        $changed = false;
        foreach($this->virtual_devices as $virtual_device) {
            $d_changed = $virtual_device->toDatabase();
            if($d_changed) {
                DeviceModManager::insertDeviceModification(DbUtils::getConnection(), $this->id,
                    $virtual_device->getDeviceId(), DeviceModManager::DEVICE_MOD_SIMPLE_SETTINGS, $issuer_id);
            }
            $changed = $changed || $d_changed;
        }

        return $changed;
    }

    public abstract function sendData(bool $quick);

    /**
     * @return bool
     */
    public function isOnline() {
        if($this->online !== null) {
            return $this->online;
        }

        $waitTimeoutInSeconds = .2;
        $fp = @fsockopen($this->hostname, $this->port, $errCode, $errStr, $waitTimeoutInSeconds);
        $this->online = $fp !== false;
        DeviceDbHelper::setOnline(DbUtils::getConnection(), $this->getId(), $this->online);
        if($this->online) {
            fclose($fp);
        }

        return $this->online;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @param string $id
     * @return int
     */
    public function getVirtualDeviceIndexById(string $id) {
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

    public function getRowHtml(int $user_id) {
        $id = urlencode($this->id);
        $display_name = urlencode($this->display_name);

        if(sizeof($this->virtual_devices) > 1) {
            $devices = "- " . Utils::getString("device_devices") . ": ";
            foreach($this->virtual_devices as $i => $device) {
                if(substr($device->getDeviceId(), 0, 12) === "virtualized_") {
                    break;
                }

                if($i > 0) {
                    $devices .= ", ";
                }

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
                    - Hostname: <i>$this->hostname:$this->port</i>
                </p>
            </div>
        </div>
    </a>
HTML;

    }

    public function getNameWithState() {
        $class = $this->isOnline() ? "invisible" : "";
        return trim("$this->display_name <span class=\"device-offline-text $class\">(" . Utils::getString("device_status_offline") . ")</span>");
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return $this->display_name;
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

    /**
     * Devices that report the state can override this to handle it
     * @param string $state - State reported by HW device. May very in format depending on device type
     */
    public function handleDeviceReportedState(/** @noinspection PhpUnusedParameterInspection */ string $state) {
        $script = __DIR__ . "/../../scripts/report_state.php";
        exec("php $script $this->owner_id >/dev/null &");
    }

    public function getHtmlHeader() {
        return "<h4>$this->display_name</h4>";
    }

    /**
     * @return string
     */
    public function getDisplayHostname(): string {
        return $this->hostname . ":" . $this->port;
    }

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @return PhysicalDevice
     */
    protected static abstract function load(string $device_id, int $owner_id, string $display_name,
                                            string $hostname, int $port, array $scopes
    );

    public static function fromDatabaseRow(array $row) {
        $device_driver = "App\\Devices\\" . $row["device_driver"];
        if(!class_exists($device_driver) || !is_subclass_of($device_driver, PhysicalDevice::class)) {
            throw new InvalidArgumentException("$device_driver is not a valid PhysicalDevice class name");
        }

        $scopes = isset($row["scope"]) ? explode(" ", $row["scope"]) : [];
        /** @noinspection PhpUndefinedMethodInspection */
        return $device_driver::load($row["id"], $row["owner_id"],
            $row["display_name"], trim($row["hostname"]), $row["port"], $scopes);
    }
}