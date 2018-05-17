<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 17:59
 */

require_once __DIR__."/ChrisWifiController.php";
require_once __DIR__."/PcLedController.php";

abstract class PhysicalDevice
{
    const ID_PC_LED_CONTROLLER = 0;

    /** @var VirtualDevice[] */
    protected $virtual_devices;

    /** @var int */
    private $id;

    /**
     * PhysicalDevice constructor.
     * @param int $id
     * @param VirtualDevice[] $virtual_devices
     */
    protected function __construct(int $id, array $virtual_devices)
    {
        $this->id = $id;
        $this->virtual_devices = $virtual_devices;
    }

    /**
     * @return bool
     */
    public abstract function isOnline();

    public abstract function save();

    /**
     * @param int $device_id
     * @return PhysicalDevice
     */
    public static abstract function load(int $device_id);

    /**
     * @param array $action
     * @return array - ex. ["status" => "SUCCESS", "ids" => [2, 5, 9]]
     */
    public abstract function handleAssistantAction(array $action);

    /**
     * @param string $id
     * @return null|VirtualDevice
     */
    public function getVirtualDeviceById(string $id)
    {
        foreach($this->virtual_devices as $virtual_device)
        {
            if($virtual_device->getDeviceId() === $id)
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

        foreach($this->virtual_devices as $virtual_device)
        {
            $name = $virtual_device->getDeviceName();
            $sanitized_name = Utils::sanitizeString($name);
            $html .= "<li class=\"nav-item\" role=\"presentation\"" .
                "><a id=\"device-link-$sanitized_name\" href=\"#$sanitized_name\" class=\"nav-link device-link\">"
                . $name . "</a></li>";
        }

        return $html;
    }

    public static function fromDatabaseRow(array $row)
    {
        switch($row["device_driver"])
        {
            case PcLedController::class:
                return PcLedController::load($row["id"]);
            case ChrisWifiController::class:
                return ChrisWifiController::load($row["id"]);
            default:
                return null;
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}