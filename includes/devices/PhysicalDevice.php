<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 17:59
 */

require_once __DIR__ . "/EspWifiLedController.php";
require_once __DIR__ . "/ChrisWifiController.php";
require_once __DIR__ . "/PcLedController.php";
require_once __DIR__ . "/../database/HomeUser.php";
require_once __DIR__ . "/../Utils.php";

abstract class PhysicalDevice
{
    const ID_PC_LED_CONTROLLER = 0;

    /** @var VirtualDevice[] */
    protected $virtual_devices;

    /** @var string */
    private $id;

    /** @var string */
    private $display_name;

    /** @var int */
    private $owner_id;

    /**
     * PhysicalDevice constructor.
     * @param string $id
     * @param int $owner_id
     * @param string $display_name
     * @param VirtualDevice[] $virtual_devices
     */
    protected function __construct(string $id, int $owner_id, string $display_name, array $virtual_devices)
    {
        $this->id = $id;
        $this->owner_id = $owner_id;
        $this->display_name = $display_name;
        $this->virtual_devices = $virtual_devices;
    }

    /**
     * @return bool
     */
    public abstract function isOnline();

    public abstract function save();

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @return PhysicalDevice
     */
    public static abstract function load(string $device_id, int $owner_id, string $display_name);

    /**
     * @param array $action
     * @param string $request_id
     * @return array - ex. ["status" => "SUCCESS", "ids" => [2, 5, 9]]
     */
    public abstract function handleAssistantAction(array $action, string $request_id);

    /**
     * @param string $id
     * @return null|VirtualDevice
     */
    public function getVirtualDeviceById(string $id)
    {
        foreach ($this->virtual_devices as $virtual_device) {
            if ($virtual_device->getDeviceId() === $id)
                return $virtual_device;
        }
        return null;
    }

    /**
     * @return VirtualDevice[]
     */
    public function getVirtualDevices(): array
    {
        return $this->virtual_devices;
    }

    public function getDeviceNavbarHtml()
    {
        $html = "";

        foreach ($this->virtual_devices as $virtual_device) {
            $name = $virtual_device->getDeviceName();
            $sanitized_name = Utils::sanitizeString($name);
            $html .= "<li class=\"nav-item\" role=\"presentation\"" .
                "><a id=\"device-link-$sanitized_name\" href=\"#$sanitized_name\" class=\"nav-link device-link\">"
                . $name . "</a></li>";
        }

        return $html;
    }

    public function getRowHtml(int $user_id)
    {
        $id = urlencode($this->id);
        $display_name = urlencode($this->display_name);
        $offline = $this->isOnline() ? "" : "<span class=\"device-offline-text\">(" . Utils::getString("device_status_offline") . ")</span>";

        if(sizeof($this->virtual_devices) > 1) {
            $devices = "- ".Utils::getString("device_devices").": ";
            foreach ($this->virtual_devices as $i => $device) {
                if ($i > 0) $devices .= ", ";
                $devices .= $device->getDeviceName();
            }
            $devices .= "<br>";
        } else {
            $devices = "";
        }

        $owner = HomeUser::queryUserById(DbUtils::getConnection(), $this->owner_id)->username;
        if ($user_id === $this->owner_id) {
            $owner = Utils::getString("device_owner_you") . " (" . $owner . ")";
        }

        return <<<HTML
<a href="/device/$display_name/$id" class="list-group-item list-group-item-action">
        <div class="row">
            <div class="col">
                <h5 class="card-title">$this->display_name $offline</h5>
                <p class="card-text">
                    $devices
                    - Owner: <b>$owner</b><br>
                </p>
            </div>
        </div>
    </a>
HTML;

    }

    public static function fromDatabaseRow(array $row)
    {
        switch ($row["device_driver"]) {
            case PcLedController::class:
                return PcLedController::load($row["id"], $row["owner_id"], $row["display_name"]);
            case ChrisWifiController::class:
                return ChrisWifiController::load($row["id"], $row["owner_id"], $row["display_name"]);
            default:
                return null;
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->display_name;
    }
}