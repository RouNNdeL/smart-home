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

require_once __DIR__."/OAuthServiceUtils.php";
require_once __DIR__."/DbUtils.php";

class OAuthService
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $client_id;

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

    public function generateAuthUrl()
    {
        $state = base64_encode(openssl_random_pseudo_bytes(1024));
        OAuthServiceUtils::insertState(DbUtils::getConnection(), $state);
        $params = [
            "client_id" => $this->client_id,
            "response_type" => "code",
            "scope" => $this->scopes,
            "redirect_uri" => OAuthServiceUtils::REDIRECT_URI,
            "state" => $state
        ];
        return $this->auth_endpoint."?". http_build_query($params);
    }

    public function requestToken($code)
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
        return $json_response["access_token"];
    }

    public static function fromId(string $id)
    {
        return self::queryById(DbUtils::getConnection(), $id);
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
            return new OAuthService($id, $name, $scopes, $client_id, $client_secret, $auth_endpoint, $token_endpoint);
        }
        $stmt->close();
        return null;
    }
}