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


use App\Database\DbUtils;
use App\OAuth\OAuthUtils;

class SmartThingsRequestManager {
    const INTERACTION_TYPE_DISCOVERY_REQUEST = "discoveryRequest";
    const INTERACTION_TYPE_DISCOVERY_RESPONSE = "discoveryResponse";
    const INTERACTION_TYPE_STATE_REFRESH_REQUEST = "stateRefreshRequest";
    const INTERACTION_TYPE_STATE_REFRESH_RESPONSE = "stateRefreshResponse";
    const INTERACTION_TYPE_COMMAND_REQUEST = "commandRequest";
    const INTERACTION_TYPE_COMMAND_RESPONSE = "commandResponse";

    public static function processRequest(array $request) {
        $token = $request["authentication"]["token"];
        $user_id = OAuthUtils::getUserIdForToken(DbUtils::getConnection(), $token);
        if($user_id == null) {
            http_response_code(400);
            return ["error" => "invalid_grant"];
        }

        $scopes = OAuthUtils::getScopesForToken(DbUtils::getConnection(), $token);
        if(strpos($scopes, OAuthUtils::SCOPE_HOME_CONTROL) === false) {
            http_response_code(400);
            return ["error" => "invalid_scope"];
        }

        $payload = [];
        $payload["headers"] = $request["headers"];

        $manager = GlobalManager::withUserOverride($user_id);
        switch($payload["headers"]["interactionType"]) {
            case self::INTERACTION_TYPE_DISCOVERY_REQUEST:
                $payload["devices"] = $manager->getSmartThingsDiscovery();
                $payload["headers"]["interactionType"] = self::INTERACTION_TYPE_DISCOVERY_RESPONSE;
                return $payload;
            case self::INTERACTION_TYPE_STATE_REFRESH_REQUEST:
                $payload["deviceState"] = $manager->getSmartThingsState();
                $payload["headers"]["interactionType"] = self::INTERACTION_TYPE_STATE_REFRESH_RESPONSE;
                return $payload;
            case self::INTERACTION_TYPE_COMMAND_REQUEST:
                $payload["deviceState"] = $manager->processSmartThingsCommand($request);
                $payload["headers"]["interactionType"] = self::INTERACTION_TYPE_COMMAND_RESPONSE;
                return $payload;
            default:
                return $request;
        }
    }
}