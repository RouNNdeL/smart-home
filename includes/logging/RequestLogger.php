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
 * Date: 2018-06-04
 * Time: 16:22
 */

require_once __DIR__ . "/../database/DbUtils.php";

class RequestLogger {
    private static $instance = null;
    private $id;

    /**
     * RequestLogger constructor.
     * @param $id
     */
    private function __construct($id) {
        $this->id = $id;
        register_shutdown_function(array(&$this, "updateHttpCodeAuto"));
    }

    public function updateHttpCodeAuto() {
        $this->updateHttpCode(http_response_code());
    }

    private function updateHttpCode(int $code) {
        $conn = DbUtils::getConnection();
        $sql = "UPDATE log_request SET http_response = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $code, $this->id);
        $stmt->execute();
        $stmt->close();
    }

    public function addDebugInfo(string $debug_info) {
        $conn = DbUtils::getConnection();
        $sql = "UPDATE log_request SET debug_info = IF(ISNULL(debug_info), ?, CONCAT(debug_info, '\n', ?)) WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $debug_info, $debug_info, $this->id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @return RequestLogger
     */
    public static function getInstance(bool $with_session_id = true) {
        if(RequestLogger::$instance === null) {
            RequestLogger::$instance = RequestLogger::create(
                $with_session_id ? SessionManager::getInstance()->getSessionId() : null,
                $_SERVER["SCRIPT_NAME"],
                $_SERVER["REQUEST_URI"],
                $_SERVER["REQUEST_METHOD"],
                $_SERVER["REMOTE_ADDR"]
            );
        }
        return RequestLogger::$instance;
    }

    /**
     * @param int $session_id
     * @param string $resource
     * @param string $uri
     * @param string $method
     * @param string $ip
     * @return RequestLogger
     */
    private static function create($session_id, string $resource, string $uri, string $method, string $ip) {
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO log_request (session_id, ip, uri, method, resource, debug_info) VALUES (?, ?, ?, ?, ?, NULL)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $session_id, $ip, $uri, $method, $resource);
        $stmt->execute();
        $stmt->close();

        return new RequestLogger($conn->insert_id);
    }
}