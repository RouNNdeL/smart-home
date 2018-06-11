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

    public static function all($log = GlobalManager::LOG)
    {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();
        $manager->loadSessionManager($log, true);
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

    public function loadSessionManager($log = GlobalManager::LOG, $login_required = false)
    {
        $this->sessionManager = SessionManager::getInstance();
        if($log)
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