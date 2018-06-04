<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-04
 * Time: 17:17
 */

require_once __DIR__ . "/database/IpTrustManager.php";
require_once __DIR__ . "/database/SessionManager.php";
require_once __DIR__ . "/UserDeviceManager.php";
require_once __DIR__ . "/logging/RequestLogger.php";

class GlobalManager
{
    const LOG = true;

    /** @var SessionManager */
    private $sessionManager = null;

    /** @var UserDeviceManager */
    private $userDeviceManager = null;

    /** @var RequestLogger */
    private $requestLogger = null;

    /** @var IpTrustManager */
    private $ipTrustManager = null;

    /**
     * GlobalManager constructor.
     */
    private function __construct()
    {

    }

    public static function minimal()
    {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();

        return $manager;
    }

    public static function withSessionManager($login_required = false)
    {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();
        $manager->loadSessionManager($login_required);

        return $manager;
    }

    public static function all()
    {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();
        $manager->loadSessionManager(true);
        $manager->loadUserDeviceManager();

        return $manager;
    }

    public function loadIpTrustManager()
    {
        $this->ipTrustManager = IpTrustManager::getInstance();
        if($this->ipTrustManager === null || !$this->ipTrustManager->isAllowed())
        {
            http_response_code(403);
            exit(0);
        }
    }

    public function loadSessionManager($login_required = false)
    {
        $this->sessionManager = SessionManager::getInstance();
        if(GlobalManager::LOG)
            $this->requestLogger = RequestLogger::getInstance();
        if($login_required && !$this->sessionManager->isLoggedIn())
        {
            require __DIR__."/../web/error/404.php";
            http_response_code(404);
            exit(0);
        }
    }

    public function loadUserDeviceManager()
    {
        $this->userDeviceManager = UserDeviceManager::fromUserId($this->sessionManager->getUserId());
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    /**
     * @return UserDeviceManager
     */
    public function getUserDeviceManager()
    {
        return $this->userDeviceManager;
    }

    /**
     * @return RequestLogger
     */
    public function getRequestLogger()
    {
        return $this->requestLogger;
    }

    /**
     * @return IpTrustManager
     */
    public function getIpTrustManager()
    {
        return $this->ipTrustManager;
    }
}