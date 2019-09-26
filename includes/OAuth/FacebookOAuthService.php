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

namespace App\OAuth;

use App\Database\HomeUser;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-14
 * Time: 19:03
 */
class FacebookOAuthService extends OAuthService {
    const ID = 2;

    const USER_INFO_ENDPOINT = "https://graph.facebook.com/v3.0/me";

    /**
     * @param array $requestTokens
     * @return HomeUser|null
     */
    public function getUser(array $requestTokens) {
        $user_id = FacebookOAuthService::queryUserData($requestTokens)["id"];
        $user = HomeUser::queryUserByFacebookId($user_id);
        return $user;
    }

    private static function queryUserData(array $requestTokens) {
        $header = [];
        $token_type = ucfirst($requestTokens["token_type"]);
        $header[] = "Authorization: $token_type $requestTokens[access_token]";

        $ch = curl_init(FacebookOAuthService::USER_INFO_ENDPOINT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $json_response = json_decode($data, true);
        curl_close($ch);
        return $json_response;
    }

    /**
     * @param array $requestTokens
     * @return HomeUser
     */
    public function registerUser(array $requestTokens) {
        $user_data = FacebookOAuthService::queryUserData($requestTokens);
        $name_arr = explode(" ", $user_data["name"]);
        return HomeUser::newUserWithFacebook($user_data["id"], $name_arr[0], end($name_arr));
    }
}