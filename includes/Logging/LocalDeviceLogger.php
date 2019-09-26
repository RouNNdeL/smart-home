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

namespace App\Logging;

use App\Database\DbUtils;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-03
 * Time: 16:53
 */


class LocalDeviceLogger {
    const TYPE_UPDATE_CHECK = "update_check";
    const TYPE_REPORT_STATE = "report_state";
    const TYPE_REPORT_HALT = "report_halt";

    public static function log(string $device_id, string $type, int $attempts, string $payload) {
        $payload = $payload === "" ? null : $payload;
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO log_device_requests 
                (device_id, type, request_attempts, payload) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $device_id, $type, $attempts, $payload);
        $stmt->execute();
        $stmt->close();
    }
}