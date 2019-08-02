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
 * Date: 2018-06-07
 * Time: 16:43
 */

require_once __DIR__ . "/DeviceModManager.php";

class ShareManager {
    const SCOPE_ANY = "%";
    const SCOPE_NONE = "_";

    const SCOPE_SIMPLE_CONTROL = "simple_control";
    const SCOPE_REBOOT = "reboot";

    const SCOPE_VIEW_EFFECTS = "view_effects";
    const SCOPE_EDIT_EFFECTS = ShareManager::SCOPE_VIEW_EFFECTS . " edit_effects";
    const SCOPE_FULL_PROFILES = ShareManager::SCOPE_EDIT_EFFECTS . " delete_effects";

    const SCOPE_VIEW_SCENES = "view_scenes";

    public static function getDevicesForScope(int $audience_id, array $scopes) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT devices_physical.id, display_name, device_driver, hostname, port, owner_id, scope FROM device_shares 
                JOIN devices_physical ON device_shares.subject_id = devices_physical.id 
                WHERE audience_id = ? AND scope LIKE ?";
        $stmt = $conn->prepare($sql);
        $like = ShareManager::getScopeLike($scopes);
        $stmt->bind_param("is", $audience_id, $like);
        $stmt->execute();

        $rows = [];
        if($result = $stmt->get_result()) {
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        $stmt->close();

        $arr = [];
        foreach($rows as $row) {
            $arr[] = PhysicalDevice::fromDatabaseRow($row);
        }

        return $arr;
    }

    public static function getScopeForModType(int $type) {
        switch($type) {
            case DeviceModManager::DEVICE_MOD_ONLINE_STATE:
            case DeviceModManager::DEVICE_MOD_SIMPLE_SETTINGS:
                return ShareManager::SCOPE_SIMPLE_CONTROL;
            case DeviceModManager::DEVICE_MOD_EFFECT:
                return ShareManager::SCOPE_VIEW_EFFECTS;
            default:
                return ShareManager::SCOPE_NONE;
        }
    }

    public static function sortScopes(array $scopes) {
        sort($scopes);
        return $scopes;
    }

    public static function getScopeLike(array $scopes) {
        $like = "%";
        foreach(ShareManager::sortScopes($scopes) as $scope) {
            $like .= "$scope ";
        }
        $like .= "%";
        return $like;
    }
}