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
 * Date: 2018-06-11
 * Time: 14:50
 */

require_once __DIR__ . "/../database/HomeUser.php";
require_once __DIR__ . "/../../vendor/autoload.php";

class GoogleOAuthService extends OAuthService {
    const ID = "1";

    const ID_TOKEN_ENDPOINT = "https://www.googleapis.com/oauth2/v3/tokeninfo";

    /**
     * @param array $requestTokens
     * @return HomeUser|null
     */
    public function getUser(array $requestTokens) {
        $id_token = GoogleOAuthService::decodeIdToken($requestTokens["id_token"]);
        if($id_token === null || isset($id_token["error_description"]))
            return null;
        return HomeUser::queryUserByGoogleId($id_token["sub"]);
    }

    private function decodeIdToken(string $id_token) {
        $fields = ["id_token" => $id_token];
        $ch = curl_init(GoogleOAuthService::ID_TOKEN_ENDPOINT . "?" . http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $json_response = json_decode($data, true);
        curl_close($ch);
        if($json_response["aud"] !== $this->client_id)
            return null;
        return $json_response;
    }

    /**
     * @param array $requestTokens
     * @return HomeUser
     */
    public function registerUser(array $requestTokens) {
        $id_token = GoogleOAuthService::decodeIdToken($requestTokens["id_token"]);
        if($id_token === null || isset($id_token["error_description"]))
            return null;
        return HomeUser::newUserWithGoogle($id_token["sub"], $id_token["given_name"], $id_token["family_name"]);
    }
}