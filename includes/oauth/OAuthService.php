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
 * Date: 2018-05-22
 * Time: 15:23
 */

require_once __DIR__ . "/OAuthServiceUtils.php";
require_once __DIR__ . "/GoogleOAuthService.php";
require_once __DIR__ . "/../database/DbUtils.php";

abstract class OAuthService
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    protected $client_id;

    /** @var string */
    private $client_secret;

    /** @var string */
    private $scopes;

    /** @var string */
    private $auth_endpoint;

    /** @var string */
    private $token_endpoint;

    /**
     * OAuthService constructor.
     * @param string $id
     * @param string $name
     * @param string $client_id
     * @param string $client_secret
     * @param string $scopes
     * @param string $auth_endpoint
     * @param string $token_endpoint
     */
    private function __construct(string $id, string $name, string $client_id, string $client_secret,
                                 string $scopes, string $auth_endpoint, string $token_endpoint
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->scopes = $scopes;
        $this->auth_endpoint = $auth_endpoint;
        $this->token_endpoint = $token_endpoint;
    }

    public function generateAuthUrl(int $session_id)
    {
        $state = base64_encode(openssl_random_pseudo_bytes(1024));
        OAuthServiceUtils::insertState(DbUtils::getConnection(), $session_id, $this->id, $state);
        $params = [
            "client_id" => $this->client_id,
            "response_type" => "code",
            "scope" => $this->scopes,
            "redirect_uri" => OAuthServiceUtils::REDIRECT_URI,
            "state" => $state
        ];
        return $this->auth_endpoint . "?" . http_build_query($params);
    }

    public function requestAndInsertTokens(int $user_id, string $auth_code)
    {
        $tokens = $this->requestTokens($auth_code);
        $this->insertToken($user_id, $tokens["access_token"], "access_token", $tokens["expires"]);
        if(isset($tokens["refresh_token"]) && $tokens["refresh_token"] !== null)
            $this->insertToken($user_id, $tokens["refresh_token"], "refresh_token", null);
    }

    /**
     * @param $code
     * @return HomeUser|null
     */
    public function getUserFromCode($code)
    {
        $requestTokens = $this->requestTokens($code);
        //TODO: Show a registration page if needed
        return $this->getUser($requestTokens);
    }

    public function requestTokens($code)
    {
        $params = [
            "code" => $code,
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri" => OAuthServiceUtils::REDIRECT_URI,
            "grant_type" => "authorization_code",
        ];

        $header = [];
        $header[] = "Content-type: application/x-www-form-urlencoded";

        $ch = curl_init($this->token_endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $json_response = json_decode($data, true);
        curl_close($ch);
        return $json_response;
    }

    public static function fromId(string $id)
    {
        return self::queryById(DbUtils::getConnection(), $id);
    }

    public static function fromSessionAndState(int $session_id, string $state)
    {
        return self::queryBySessionAndState(DbUtils::getConnection(), $session_id, $state);
    }

    private function insertToken(int $user_id, string $token_type, string $token, $expires)
    {
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO service_tokens (user_id, issuer_id, token, type, expires) VALUES 
                (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL SECOND " . $expires . "))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $this->id, $token, $token_type);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    private static function queryBySessionAndState(mysqli $conn, int $session_id, string $state)
    {
        $sql = "SELECT service_id FROM service_auth_states WHERE session_id = ? AND state = ? 
                AND DATE_ADD(date, INTERVAL 30 MINUTE) > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $session_id, $state);
        $stmt->bind_result($id);
        $stmt->execute();
        if(!$stmt->fetch())
        {
            $stmt->close();
            return null;
        }
        $stmt->close();
        $sql = "DELETE FROM service_auth_states WHERE session_id = ? AND state = ? 
                AND DATE_ADD(date, INTERVAL 30 MINUTE) > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $session_id, $state);
        $stmt->execute();
        $stmt->close();
        return OAuthService::fromId($id);
    }

    private static function queryById(mysqli $conn, string $id)
    {
        $sql = "SELECT id, name, client_id, client_secret, scopes, auth_endpoint, token_endpoint
                FROM service_issuers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->bind_result($id, $name, $scopes, $client_id, $client_secret, $auth_endpoint, $token_endpoint);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            switch($id)
            {
                case GoogleOAuthService::ID:
                    return new GoogleOAuthService($id, $name, $scopes, $client_id, $client_secret, $auth_endpoint, $token_endpoint);
            }
        }
        $stmt->close();
        return null;
    }

    /**
     * @param array $requestTokens
     * @return HomeUser|null
     */
    public abstract function getUser(array $requestTokens);
}