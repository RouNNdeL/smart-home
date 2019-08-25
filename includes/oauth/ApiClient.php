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
 * Time: 15:17
 */
class ApiClient {
    public $id;
    public $name;
    public $secret;
    private $grant_types;

    /**
     * ApiClient constructor.
     * @param $id
     * @param $name
     * @param $secret
     */
    private function __construct($id, $name, $secret, array $grant_types) {
        $this->id = $id;
        $this->name = $name;
        $this->secret = $secret;
        $this->grant_types = $grant_types;
    }

    public function supportsGrantType($grant_type) {
        return in_array($grant_type, $this->grant_types);
    }

    /**
     * @param $conn mysqli
     * @param $id
     * @return ApiClient|null
     */
    public static function queryClientById($conn, string $id) {
        $sql = "SELECT id, name, secret, grant_types FROM api_clients WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->bind_result($id, $name, $secret, $grant_types);
        $stmt->execute();

        if($stmt->fetch()) {
            return new ApiClient($id, $name, $secret, explode(" ", $grant_types));
        }

        $stmt->close();
        return null;
    }
}