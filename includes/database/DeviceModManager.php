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
 * Date: 2018-12-14
 * Time: 21:56
 */

require_once __DIR__ . "/ShareManager.php";

class DeviceModManager {
    const DEVICE_MOD_UNKNOWN = -1;
    const DEVICE_MOD_ONLINE_STATE = 0x10;
    const DEVICE_MOD_SIMPLE_SETTINGS = 0x11;
    const DEVICE_MOD_EFFECT = 0x12;

    public static function insertDeviceModification(mysqli $conn, string $physical_id, $virtual_id, int $type, string $issuer_id) {
        $sql = "INSERT INTO device_modifications (physical_id, virtual_id, type, issuer_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $physical_id, $virtual_id, $type, $issuer_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    private static function getStatementForParams(mysqli $conn, string $sql, int $user_id, $physical_id, $virtual_id, array $types) {
        $where = "";
        $vars = "";
        $count = 0;
        $func_params = [0];

        if($physical_id !== null) {
            if($count > 0)
                $where .= " AND ";
            $where .= "physical_id = ?";
            $vars .= "s";
            $count++;
            $func_params[] = &$physical_id;
        }

        if($virtual_id !== null) {
            if($count > 0)
                $where .= " AND ";
            $where .= "virtual_id = ?";
            $vars .= "s";
            $count++;
            $func_params[] = &$virtual_id;
        }

        $scopes = [];

        if($count > 0)
            $where .= " AND ";
        $where .= "type IN (";
        foreach($types as $i => $t) {
            if($i)
                $where .= ", ";
            $scopes[] = ShareManager::getScopeForModType($t);
            $where .= "?";
            $vars .= "i";
            $count++;
            $func_params[] = &$types[$i];
        }
        $where .= ")";

        $scope_like = ShareManager::getScopeLike($scopes);

        if($count > 0)
            $where .= " AND ";
        $where .= "(owner_id = ? OR (audience_id = ? AND scope LIKE ?))";
        $vars .= "iis";
        $count += 3;
        $func_params[] = &$user_id;
        $func_params[] = &$user_id;
        $func_params[] = &$scope_like;

        $func_params[0] = &$vars;
        $query = str_replace("%WHERE%", "($where)", $sql);
        $stmt = $conn->prepare($query);
        call_user_func_array([$stmt, "bind_param"], $func_params);
        return $stmt;
    }

    public static function getLastModDate(mysqli $conn, int $user_id, $physical_id, $virtual_id, $types) {
        $sql = "SELECT date FROM device_modifications 
                    JOIN devices_physical dp ON device_modifications.physical_id = dp.id 
                    LEFT JOIN device_shares share2 on dp.id = share2.subject_id and dp.owner_id = share2.issuer_id
                    WHERE %WHERE% ORDER BY date DESC LIMIT 1";
        $stmt = DeviceModManager::getStatementForParams($conn, $sql, $user_id, $physical_id, $virtual_id, $types);
        $stmt->bind_result($date);
        $stmt->execute();
        if($stmt->fetch()) {
            $stmt->close();
            return $date;
        }

        $stmt->close();
        return null;
    }

    /**
     * @param mysqli $conn
     * @param string $last_date - Use caution, variable is not sanitized (it should be pulled from the getLastModDate method anyways)
     * @param int $user_id
     * @param $physical_id
     * @param $virtual_id
     * @param $type
     * @return null
     */
    public static function queryNewMods(mysqli $conn, string $last_date, int $user_id, $physical_id, $virtual_id, $types) {
        $sql = "SELECT device_modifications.id, date, physical_id, virtual_id, type, device_modifications.issuer_id FROM device_modifications 
                    JOIN devices_physical dp ON device_modifications.physical_id = dp.id 
                    LEFT JOIN device_shares share2 on dp.id = share2.subject_id and dp.owner_id = share2.issuer_id
                    WHERE date > '$last_date' AND %WHERE% ORDER BY date";

        $stmt = DeviceModManager::getStatementForParams($conn, $sql, $user_id, $physical_id, $virtual_id, $types);
        $stmt->bind_result($id, $_date, $_physical_id, $_virtual_id, $_type, $issuer_id);
        $stmt->execute();

        $array = [];
        while($stmt->fetch()) {
            $array[$id] = ["date" => $_date, "physical_id" => "$_physical_id",
                "virtual_id" => $_virtual_id, "type" => "$_type", "issuer_id" => "$issuer_id"];
        }

        $stmt->close();
        return $array;
    }


}