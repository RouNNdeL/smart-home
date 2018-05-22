<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-22
 * Time: 11:22
 */

require_once __DIR__."/DbUtils.php";

class IpTrustManager
{
    const HEAT_LOGIN_ATTEMPT = 10;
    const HEAT_SUCCESSFUL_LOGIN = -25;
    const HEAT_UNTRUSTED = 50;
    const HEAT_DENIED = 250;
    const HEAT_BLACKLIST_BONUS = 100;
    const HEAT_LOCAL_BONUS = -50;

    /** @var string */
    private $ip;

    /** @var int */
    private $heat;

    /**
     * IpTrustManager constructor.
     * @param string $ip
     * @param int $heat
     */
    private function __construct(string $ip, int $heat)
    {
        $this->ip = $ip;
        $this->heat = $heat;
    }

    /**
     * @param string $ip
     * @return IpTrustManager|null
     */
    public static function fromIp(string $ip)
    {
        if(!self::isValid($ip))
            return null;
        $manager = IpTrustManager::queryByIp(DbUtils::getConnection(), $ip);
        if($manager !== null)
            return $manager;
        $manager = new IpTrustManager($ip, 0);
        $manager->insertOrUpdate();
        return $manager;
    }

    private static function queryByIp(mysqli $conn, string $ip)
    {
        $sql = "SELECT heat_value FROM ip_heat WHERE ip = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        $stmt->bind_result($heat);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return new IpTrustManager($ip, $heat);
        }
        $stmt->close();
        return null;
    }

    private function insertOrUpdate()
    {
        $sql = "INSERT INTO ip_heat (ip, heat_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE heat_value = ?";
        $stmt = DbUtils::getConnection()->prepare($sql);
        $stmt->bind_param("sii", $this->ip, $this->heat, $this->heat);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function isTrusted()
    {
        return $this->getHeat() < self::HEAT_UNTRUSTED;
    }

    public function isAllowed()
    {
        return $this->getHeat() < self::HEAT_DENIED;
    }

    public function heatUp(int $value)
    {
        $this->heat += $value;
        $this->insertOrUpdate();
    }

    private static function isValid(string $ip)
    {
        $re = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/';
        preg_match($re, $ip, $match);
        return $match !== null && sizeof($match) > 0;
    }

    private static function isInBlacklist(string $ip)
    {
        preg_match(
            "/^" . preg_quote($ip) . "/",
            file_get_contents(__DIR__ . "/../../ip_blacklist.txt"),
            $match
        );
        return $match !== null && sizeof($match) > 0;
    }

    private static function isLocal(string $ip)
    {
        preg_match(
            "/(^127\.)|(^10\.)|(^172\.1[6-9]\.)|(^172\.2[0-9]\.)|(^172\.3[0-1]\.)|(^192\.168\.)/",
            $ip,
            $match
        );
        return $match !== null && sizeof($match) > 0 && IpTrustManager::isValid($ip);
    }

    /**
     * @return int
     */
    public function getHeat(): int
    {
        return $this->heat + (self::isInBlacklist($this->ip) ? self::HEAT_BLACKLIST_BONUS : 0) +
            (self::isLocal($this->ip) ? self::HEAT_LOCAL_BONUS : 0) ;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

}