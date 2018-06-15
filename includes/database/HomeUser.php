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
 * Date: 2018-02-16
 * Time: 19:35
 */

require_once __DIR__."/DbUtils.php";

class HomeUser
{
    /** @var int */
    public $id;
    /** @var string */
    public $username;
    /** @var string */
    public $secret;
    /** @var bool */
    public $registered_for_report_state;
    /** @var string */
    public $first_name;
    /** @var string */
    public $last_name;

    /**
     * HomeUser constructor.
     * @param int $id
     * @param string $username
     * @param string $first_name
     * @param string $last_name
     * @param string $secret
     * @param bool $registered_for_report_state
     */
    private function __construct(int $id, $username, $first_name, $last_name, $secret, bool $registered_for_report_state)
    {
        $this->id = $id;
        $this->username = $username;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->secret = $secret;
        $this->registered_for_report_state = $registered_for_report_state;
    }

    public function formatName($short = false)
    {
        if($short)
        {
            if($this->first_name !== null)
                return $this->first_name;
            return $this->username;
        }
        else
        {
            if($this->first_name !== null && $this->last_name !== null)
                return $this->first_name." ".$this->last_name;
            return $this->username;
        }
    }

    /**
     * @param string $google_id
     * @param $first_name
     * @param $last_name
     * @return HomeUser|null
     */
    public static function newUserWithGoogle(string $google_id, $first_name, $last_name)
    {
        $conn = DbUtils::getConnection();
        $stmt = $conn->prepare("INSERT INTO home_users (google_id, first_name, last_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $google_id, $first_name, $last_name);
        $result = $stmt->execute();
        $stmt->close();
        if($result === false)
            return null;
        return self::queryUserByGoogleId($google_id);
    }

    /**
     * @param string $facebook_id
     * @param $first_name
     * @param $last_name
     * @return HomeUser|null
     */
    public static function newUserWithFacebook(string $facebook_id, $first_name, $last_name)
    {
        $conn = DbUtils::getConnection();
        $stmt = $conn->prepare("INSERT INTO home_users (facebook_id, first_name, last_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $facebook_id, $first_name, $last_name);
        $result = $stmt->execute();
        $stmt->close();
        if($result === false)
            return null;
        return self::queryUserByFacebookId($facebook_id);
    }

    public static function newUser(mysqli $conn, string $username, string $password)
    {
        $password_hash = self::hashPassword($password);
        if(self::insertUser($conn, $username, $password_hash) === false)
        {
            return null;
        }
        return self::queryUserByUsername($conn, $username);
    }

    private static function hashPassword(string $password): string
    {
        $options = ["cost" => 12];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public static function verifyPassword(string $password, string $hash): string
    {
        return password_verify($password, $hash);
    }

    /**
     * @param $conn mysqli
     * @param $username
     * @param $password_hashed
     * @return bool
     */
    private static function insertUser(mysqli $conn, string $username, string $password_hashed)
    {
        $stmt = $conn->prepare("INSERT INTO home_users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password_hashed);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param mysqli $conn mysqli
     * @param string $username
     * @return HomeUser|null
     */
    public static function queryUserByUsername(mysqli $conn, string $username)
    {
        $sql = "SELECT id, username, first_name, last_name, secret_2fa, actions_registered FROM home_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->fetch();
        $result = $stmt->get_result();
        $stmt->close();
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new HomeUser($row["id"], $row["username"], $row["first_name"], $row["last_name"],
                $row["secret_2fa"], $row["google_registered"]
            );
        }

        return null;
    }

    /**
     * @param $conn mysqli
     * @param $id
     * @return HomeUser|null
     */
    public static function queryUserById(mysqli $conn, int $id)
    {
        $sql = "SELECT id, username, first_name, last_name, secret_2fa, actions_registered FROM home_users WHERE id = $id";
        $result = $conn->query($sql);
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new HomeUser($row["id"], $row["username"], $row["first_name"], $row["last_name"],
                $row["secret_2fa"], $row["actions_registered"]
            );
        }

        return null;
    }


    /**
     * @param string $id
     * @return HomeUser|null
     */
    public static function queryUserByGoogleId(string $id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, username, first_name, last_name, secret_2fa, actions_registered FROM home_users WHERE google_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->bind_result($id, $username, $first_name, $last_name, $secret, $actions_registred);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return new HomeUser($id, $username, $first_name, $last_name, $secret, $actions_registred);
        }
        $stmt->close();
        return null;
    }


    /**
     * @param string $id
     * @return HomeUser|null
     */
    public static function queryUserByFacebookId(string $id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, username, first_name, last_name, secret_2fa, actions_registered FROM home_users WHERE facebook_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->bind_result($id, $username, $first_name, $last_name, $secret, $actions_registred);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return new HomeUser($id, $username, $first_name, $last_name, $secret, $actions_registred);
        }
        $stmt->close();
        return null;
    }

    /**
     * @param $conn mysqli
     * @param $id
     * @return bool|mysqli_result
     */
    public static function enableUserById(mysqli $conn, int $id)
    {
        $sql = "UPDATE home_users SET enabled = 1 WHERE id = $id";
        return $conn->query($sql);
    }

    /**
     * @param $conn mysqli
     * @param int $id
     * @param bool $registered
     * @return bool|mysqli_result
     */
    public static function setActionsRegistered(mysqli $conn, int $id, bool $registered)
    {
        $val = $registered ? 1 : 0;
        $sql = "UPDATE home_users SET actions_registered = $val WHERE id = $id";
        return $conn->query($sql);
    }

    /**
     * @param mysqli $conn
     * @return HomeUser[]
     */
    public static function queryAllRegistered(mysqli $conn)
    {
        $sql = "SELECT id, username, first_name, last_name, secret_2fa FROM home_users WHERE actions_registered = 1";
        $result = $conn->query($sql);
        $arr = [];

        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                $arr[] = new HomeUser($row["id"], $row["username"], $row["first_name"], $row["last_name"],
                    $row["secret_2fa"], true
                );
            }
        }

        return $arr;
    }
}