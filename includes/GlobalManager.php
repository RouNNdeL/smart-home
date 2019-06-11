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
 * Date: 2018-06-04
 * Time: 17:17
 */

require_once __DIR__ . "/database/IpTrustManager.php";
require_once __DIR__ . "/database/SessionManager.php";
require_once __DIR__ . "/UserDeviceManager.php";
require_once __DIR__ . "/logging/RequestLogger.php";

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

    /**
     * GlobalManager constructor.
     */
    private function __construct() {

    }

    /**
     * Only use when user has already been authenticated
     * @param int $user_id
     */
    public static function forWebhook(int $user_id) {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();
        $manager->loadExtensionManagers($user_id);
        $manager->loadUserDeviceManagerManually($user_id);

        return $manager;
    }

    public static function minimal() {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();

        return $manager;
    }

    public static function withSessionManager($login_required = false, $log = GlobalManager::LOG) {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();
        $manager->loadSessionManager($login_required, $log);

        return $manager;
    }

    public static function all($scopes = null, $log = GlobalManager::LOG) {
        $manager = new GlobalManager();

        $manager->loadIpTrustManager();
        $manager->loadSessionManager(true, $log);
        $manager->loadUserDeviceManager($scopes);

        return $manager;
    }

    public function loadIpTrustManager() {
        $this->ipTrustManager = IpTrustManager::getInstance();
        if($this->ipTrustManager === null || !$this->ipTrustManager->isAllowed()) {
            http_response_code(403);
            exit(0);
        }
    }

    public function loadSessionManager($login_required = false, $log = GlobalManager::LOG) {
        $this->sessionManager = SessionManager::getInstance();
        if($log)
            $this->requestLogger = RequestLogger::getInstance();
        if($login_required && !$this->sessionManager->isLoggedIn()) {
            header("Location: /");
            exit(0);
        }
    }

    public function loadUserDeviceManager($scopes = null) {
        if($scopes === null)
            $this->userDeviceManager = UserDeviceManager::forUserId($this->sessionManager->getUserId());
        else
            $this->userDeviceManager = UserDeviceManager::forUserIdAndScope($this->sessionManager->getUserId(), $scopes);
    }

    public function loadUserDeviceManagerManually(int $user_id){
        $this->userDeviceManager = UserDeviceManager::forUserId($user_id);
    }

    public function loadExtensionManagers(int $user_id) {
        $this->extensionManagers = ExtensionManager::getExtensionManagersByUserId($user_id);
    }

    public function actionsGetSync() {
        $payload = $this->userDeviceManager->getSync();
        foreach($this->extensionManagers as $extensionManager) {
            $payload = array_merge($payload, $extensionManager->getSync());
        }
        return $payload;
    }

    public function actionsProcessQuery(array $payload) {
        $response = $this->userDeviceManager->processQuery($payload);
        foreach($this->extensionManagers as $extensionManager) {
            $response = array_merge($response, $extensionManager->processQuery($payload));
        }
        return $response;
    }

    public function actionsProcessExecute(array $payload) {
        $response = $this->userDeviceManager->processExecute($payload);
        foreach($this->extensionManagers as $extensionManager) {
            $response = array_merge($response, $extensionManager->processExecute($payload));
        }
        return $response;
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
}