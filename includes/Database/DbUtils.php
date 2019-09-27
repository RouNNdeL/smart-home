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
 * Date: 2018-02-17
 * Time: 11:05
 */

namespace App\Database;

use mysqli;

require_once __DIR__ . "/../../secure_config.php";

class DbUtils {
    /** @var mysqli */
    private static $connection = null;

    /**
     * @return mysqli
     */
    public static function getConnection() {

        if(self::$connection === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            return self::$connection = new mysqli("localhost", DB_USERNAME, DB_PASSWORD, "smart_home");
        } else {
            return self::$connection;
        }
    }

    public static function getSecret(string $id) {
        $sql = "SELECT value FROM secrets WHERE id = ?";
        $stmt = DbUtils::$connection->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->bind_result($value);
        $stmt->execute();
        if($stmt->fetch()) {
            $stmt->close();
            return $value;
        } else {
            $stmt->close();
            return null;
        }
    }
}