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

namespace App;

use App\Database\{IpTrustManager, SessionManager};
use App\Logging\RequestLogger;
use App\RemoteActions\RemoteActionManager;
use UnexpectedValueException;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-04
 * Time: 17:17
 */
class GlobalManager {
    const LOG = true;

    /** @var SessionManager */
    private $sessionManager = null;

    /** @var UserDeviceManager */
    private $userDeviceManager = null;

    /** @var ExtensionManager[] */
    private $extensionManagers = [];

    /** @var RequestLogger */
    private $requestLogger = null;

    /** @var IpTrustManager */
    private $ipTrustManager = null;

    private static $instance = null;

    /**
     * GlobalManager constructor.
     */
    private function __construct() {

    }

    /**
     * @return RemoteActionManager
     */
    public function getRemoteActionManager(int $user_id = null): RemoteActionManager {
        foreach($this->extensionManagers as $extensionManager) {
            if($extensionManager instanceof RemoteActionManager) {
                return $extensionManager;
            }
        }

        if($user_id === null) {
            $user_id = $this->sessionManager->getUserId();
        }

        $this->loadExtensionManagers($user_id);
        foreach($this->extensionManagers as $extensionManager) {
            if($extensionManager instanceof RemoteActionManager) {
                return $extensionManager;
            }
        }

        throw new UnexpectedValueException("Unable to load RemoteActionManager");
    }

    /**
     * @return GlobalManager
     */
    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new GlobalManager();
        }
        return self::$instance;
    }

    public function actionsGetSync() {
        $payload = $this->userDeviceManager->getActionsSync();
        foreach($this->extensionManagers as $extensionManager) {
            $payload = array_merge($payload, $extensionManager->getActionsSync());
        }
        return $payload;
    }

    public function actionsProcessQuery(array $payload) {
        $response = $this->userDeviceManager->processActionsQuery($payload);
        foreach($this->extensionManagers as $extensionManager) {
            $response = array_merge($response, $extensionManager->processActionsQuery($payload));
        }
        return $response;
    }

    public function actionsProcessExecute(array $payload) {
        $response = $this->userDeviceManager->processActionsExecute($payload);
        foreach($this->extensionManagers as $extensionManager) {
            $response = array_merge($response, $extensionManager->processActionsExecute($payload));
        }
        return $response;
    }

    public function getSmartThingsDiscovery() {
        $payload = $this->userDeviceManager->getSmartThingsDiscovery();
        foreach($this->extensionManagers as $extensionManager) {
            $payload = array_merge($payload, $extensionManager->getSmartThingsDiscovery());
        }
        return $payload;
    }

    public function processSmartThingsCommand(array $payload) {
        $response = $this->userDeviceManager->processSmartThingsCommand($payload);
        foreach($this->extensionManagers as $extensionManager) {
            $manager_response = $extensionManager->processSmartThingsCommand($payload);
            if($manager_response !== null) {
                $response = array_merge($response, $manager_response);
            }
        }
        return $response;
    }

    public function getSmartThingsState() {
        $payload = $this->userDeviceManager->getSmartThingsState();
        foreach($this->extensionManagers as $extensionManager) {
            $smartThingsState = $extensionManager->getSmartThingsState();
            if($smartThingsState !== null) {
                $payload = array_merge($payload, $smartThingsState);
            }
        }
        return $payload;
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager() {
        return $this->sessionManager;
    }

    /**
     * @return UserDeviceManager
     */
    public function getUserDeviceManager() {
        return $this->userDeviceManager;
    }

    /**
     * @return RequestLogger
     */
    public function getRequestLogger() {
        return $this->requestLogger;
    }

    /**
     * @return IpTrustManager
     */
    public function getIpTrustManager() {
        return $this->ipTrustManager;
    }

    /**
     * Only use when user has already been authenticated
     * @param int $user_id
     * @return GlobalManager
     */
    public static function withUserOverride(int $user_id, bool $ip_trust_manager = true) {
        $manager = GlobalManager::getInstance();

        if($ip_trust_manager) {
            $manager->loadIpTrustManager();
        }
        $manager->loadExtensionManagers($user_id);
        $manager->loadUserDeviceManagerManually($user_id);

        return $manager;
    }

    public function loadIpTrustManager() {
        $this->ipTrustManager = IpTrustManager::getInstance();
        if($this->ipTrustManager === null || !$this->ipTrustManager->isAllowed()) {
            http_response_code(403);
            exit(0);
        }
    }

    public function loadExtensionManagers(int $user_id) {
        $this->extensionManagers = ExtensionManager::getExtensionManagersByUserId($user_id);
    }

    public function loadUserDeviceManagerManually(int $user_id) {
        $this->userDeviceManager = UserDeviceManager::forUserId($user_id);
    }

    public static function minimal($log = GlobalManager::LOG) {
        $manager = GlobalManager::getInstance();

        if($log) {
            $manager->requestLogger = RequestLogger::getInstance();
        }
        $manager->loadIpTrustManager();

        return $manager;
    }

    public static function withSessionManager($login_required = false, $log = GlobalManager::LOG) {
        $manager = GlobalManager::getInstance();

        $manager->loadIpTrustManager();
        $manager->loadSessionManager($login_required, $log);

        return $manager;
    }

    public function loadSessionManager($login_required = false, $log = GlobalManager::LOG) {
        $this->sessionManager = SessionManager::getInstance();
        if($log) {
            $this->requestLogger = RequestLogger::getInstance();
        }
        if($login_required && !$this->sessionManager->isLoggedIn()) {
            header("Location: /");
            exit(0);
        }
    }

    public static function all($scopes = null, $log = GlobalManager::LOG) {
        $manager = GlobalManager::getInstance();

        $manager->loadIpTrustManager();
        $manager->loadSessionManager(true, $log);
        $manager->loadUserDeviceManager($scopes);

        return $manager;
    }

    public function loadUserDeviceManager($scopes = null) {
        if($scopes === null)
            $this->userDeviceManager = UserDeviceManager::forUserId($this->sessionManager->getUserId());
        else
            $this->userDeviceManager = UserDeviceManager::forUserIdAndScope($this->sessionManager->getUserId(), $scopes);
    }
}