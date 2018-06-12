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
 * Date: 2018-05-27
 * Time: 21:01
 */

require_once __DIR__ . "/DbUtils.php";
require_once __DIR__ . "/IpTrustManager.php";
require_once __DIR__ . "/HomeUser.php";

class SessionManager
{
    const SESSION_COOKIE = "session";
    const CAPTCHA_HOST = "https://www.google.com/recaptcha/api/siteverify";
    const CAPTCHA_SECRET = "captcha_secret";

    private $session_id;

    private $session_token;

    private $user_id;


    private static $instance = null;

    /**
     * SessionManager constructor.
     * @param $session_id
     * @param $session_token
     * @param $user_id
     */
    private function __construct(int $session_id, $session_token, $user_id)
    {
        $this->session_id = $session_id;
        $this->session_token = $session_token;
        $this->user_id = $user_id;
    }

    /**
     * @return SessionManager
     */
    public static function getInstance()
    {
        if(SessionManager::$instance === null)
        {
            $session_token = (isset($_COOKIE[self::SESSION_COOKIE]) && $_COOKIE[self::SESSION_COOKIE] !== null) ?
                $_COOKIE[self::SESSION_COOKIE] : "";
            $agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER['HTTP_USER_AGENT'] : "NOT_SET";
            SessionManager::$instance = SessionManager::fromSessionToken(
                $session_token,
                $_SERVER["REMOTE_ADDR"],
                $agent
            );
        }
        return SessionManager::$instance;
    }

    public static function fromSessionToken(string $session_token, string $ip, string $agent)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, user_id FROM sessions WHERE sessions.token = ? AND expires > NOW() AND valid = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $session_token);
        $stmt->bind_result($id, $user_id);
        $stmt->execute();
        if($stmt->fetch())
        {
            $manager = new SessionManager($id, $session_token, $user_id);
        }
        else
        {
            $manager = SessionManager::newAnonymous($conn);
            $manager->setCookie();
        }

        $stmt->close();

        $manager->updateSession($conn, $ip, $agent);
        return $manager;
    }

    public function isLoggedIn()
    {
        return $this->user_id !== null;
    }

    public function attemptLoginAuto(string $username, string $password)
    {
        return $this->attemptLogin($username, $password, $_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]);
    }

    public function attemptLogin(string $username, string $password, string $ip, string $agent)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT password, id FROM home_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->bind_result($password_hash, $user_id);
        $stmt->execute();
        if(!$stmt->fetch())
        {
            $stmt->close();
            return false;
        }
        if(!HomeUser::verifyPassword($password, $password_hash))
        {
            $stmt->close();
            SessionManager::insertLoginAttempt($user_id, $this->session_id, $ip, false);
            return false;
        }
        $stmt->close();

        SessionManager::insertLoginAttempt($user_id, $this->session_id, $ip, true);

        $manager = SessionManager::createNew($user_id, $ip);
        $this->invalidate();
        $this->session_id = $manager->session_id;
        $this->user_id = $manager->user_id;
        $this->session_token = $manager->session_token;
        $this->updateSession($conn, $ip, $agent, true);

        return true;
    }

    public function forceLoginAuto(int $user_id)
    {
        $this->forceLogin($user_id, $_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]);
    }

    public function forceLogin(int $user_id, string $ip, string $agent)
    {
        $conn = DbUtils::getConnection();
        SessionManager::insertLoginAttempt($user_id, $this->session_id, $ip, true);

        $manager = SessionManager::createNew($user_id, $ip);
        $this->invalidate();
        $this->session_id = $manager->session_id;
        $this->user_id = $manager->user_id;
        $this->session_token = $manager->session_token;
        $this->updateSession($conn, $ip, $agent, true);
    }

    public static function newAnonymous(mysqli $conn)
    {
        $session_token = SessionManager::generateSessionToken();
        $sql = "INSERT INTO sessions (token, expires) VALUES (?, DATE_ADD(NOW(), INTERVAL 3 DAY))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $session_token);
        $stmt->execute();
        $stmt->close();

        return new SessionManager($conn->insert_id, $session_token, null);
    }

    private static function createNew(int $user_id, string $ip)
    {
        $conn = DbUtils::getConnection();
        $session_token = SessionManager::generateSessionToken();
        $local = IpTrustManager::isLocal($ip);
        $sql = $local ?
            "INSERT INTO sessions (user_id, token, expires) VALUES ($user_id,  ?, DATE_ADD(NOW(), INTERVAL 3 DAY))" :
            "INSERT INTO sessions (user_id, token) VALUES ($user_id,  ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $session_token);
        $stmt->execute();
        $stmt->close();

        return new SessionManager($conn->insert_id, $session_token, $user_id);
    }

    private function setCookie()
    {
        setcookie(self::SESSION_COOKIE, $this->session_token, time() + 3 * 24 * 60 * 60, "/", "zdul.xyz", true, true);
    }

    private function updateSession(mysqli $conn, string $ip, string $agent, bool $force_refresh = false)
    {
        if($force_refresh || IpTrustManager::isLocal($ip))
        {
            $this->setCookie();
            $sql = "UPDATE sessions SET 
                      expires = DATE_ADD(NOW(), INTERVAL 3 DAY), 
                      last_active = NOW(),
                      last_ip = ?,
                      last_agent = ?
                    WHERE id = ?";
        }
        else
        {
            $sql = "UPDATE sessions SET
                      last_active = NOW(),
                      last_ip = ?,
                      last_agent = ?
                    WHERE id = ?";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $ip, $agent, $this->session_id);
        $stmt->execute();
        $stmt->close();
    }

    private function invalidate()
    {
        $conn = DbUtils::getConnection();

        $sql = "UPDATE sessions SET valid = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $this->session_id);
        $stmt->execute();
        $stmt->close();
    }

    private static function insertLoginAttempt(int $user_id, int $session_id, string $ip, bool $success)
    {
        if($session_id == -1)
            $session_id = null;
        $sql = "INSERT INTO login_attempts 
                  (user_id, session_id, ip_address, success) 
                VALUES (?, ?,  ?, ?)";
        $stmt = DbUtils::getConnection()->prepare($sql);
        $val = $success ? 1 : 0;
        $stmt->bind_param("iisi", $user_id, $session_id, $ip, $val);
        return $stmt->execute();
    }

    private static function generateSessionToken()
    {
        return base64_encode(openssl_random_pseudo_bytes(512));
    }

    public static function validateCaptchaAuto(string $token)
    {
        return SessionManager::validateCaptcha($token, $_SERVER["REMOTE_ADDR"]);
    }

    private static function validateCaptcha(string $token, string $ip)
    {
        $post_fields = [
            "secret" => DbUtils::getSecret(SessionManager::CAPTCHA_SECRET),
            "response" => $token,
            "remoteip" => $ip
        ];

        $ch = curl_init(SessionManager::CAPTCHA_HOST);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $json_response = json_decode($data, true);
        curl_close($ch);
        return $json_response["success"];
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getSessionId(): int
    {
        return $this->session_id;
    }
}