<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 17:59
 */

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
     * @return PhysicalDevice
     */
    public static abstract function load();

    public abstract function handleAssistantAction(array $action);

    /**
     * @param int $id
     * @return null|VirtualDevice
     */
    public function getVirtualDeviceById(int $id)
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
                return PcLedController::load();
            case ChrisWifiController::class:
                return ChrisWifiController::load();
            default:
                return null;
        }
    }
}