<?php
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

    private $session_id;

    private $session_token;

    private $user_id;

    /**
     * SessionManager constructor.
     * @param $session_id
     * @param $session_token
     * @param $user_id
     */
    public function __construct(int $session_id, $session_token, $user_id)
    {
        $this->session_id = $session_id;
        $this->session_token = $session_token;
        $this->user_id = $user_id;
    }

    public static function auto()
    {
        $session_token = isset($_COOKIE[self::SESSION_COOKIE]) ? $_COOKIE[self::SESSION_COOKIE] : "";
        return SessionManager::fromSessionToken(
            $session_token,
            $_SERVER["REMOTE_ADDR"],
            $_SERVER['HTTP_USER_AGENT']
        );
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
            $manager = new SessionManager($id, $session_token, $user_id);
        else
            $manager = SessionManager::newAnonymous($conn, $ip);

        $stmt->close();

        $manager->setCookie();
        $manager->updateSession($conn, $ip, $agent);
        return $manager;
    }

    public function isLoggedIn()
    {
        return $this->user_id !== null;
    }

    public function attemptLoginAuto(string $username, string $password)
    {
        return $this->attemptLogin($username, $password,  $_SERVER["REMOTE_ADDR"], $_SERVER["HTTP_USER_AGENT"]);
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
            SessionManager::insertLoginAttempt($user_id, -1, $ip);
            $stmt->close();
            return false;
        }
        $stmt->close();

        $manager = SessionManager::createNew($user_id, $ip);
        $this->invalidate();
        $this->session_id = $manager->session_id;
        $this->user_id = $manager->user_id;
        $this->session_token = $manager->session_token;
        $this->updateSession($conn, $ip, $agent);
        $this->setCookie();

        SessionManager::insertLoginAttempt($user_id, $this->session_id, $ip);

        return true;
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
        setcookie(self::SESSION_COOKIE, $this->session_token, 0, "/", "zdul.xyz", true, true);
    }

    private function updateSession(mysqli $conn, string $ip, string $agent)
    {
        if(IpTrustManager::isLocal($ip))
        {
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
        $stmt->bind_param("i",  $this->session_id);
        $stmt->execute();
        $stmt->close();
    }

    private static function insertLoginAttempt(int $user_id, int $session_id, string $ip)
    {
        if($session_id == -1)
            $session_id = null;
        $sql = "INSERT INTO login_attempts 
                  (user_id, session_id, ip_address) 
                VALUES ($user_id, $session_id,  ?)";
        $stmt = DbUtils::getConnection()->prepare($sql);
        $stmt->bind_param("s", $ip);
        return $stmt->execute();
    }

    private static function generateSessionToken()
    {
        return base64_encode(openssl_random_pseudo_bytes(1024));
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}