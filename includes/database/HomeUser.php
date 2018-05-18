<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-16
 * Time: 19:35
 */

class HomeUser
{
    public $id;
    public $username;
    public $secret;
    public $registered_for_report_state;

    /**
     * HomeUser constructor.
     * @param int $id
     * @param string $username
     * @param string $secret
     * @param bool $registered_for_report_state
     */
    private function __construct(int $id, string $username, string $secret, bool $registered_for_report_state)
    {
        $this->id = $id;
        $this->username = $username;
        $this->secret = $secret;
        $this->registered_for_report_state = $registered_for_report_state;
    }

    public static function newUser($conn, $username)
    {
        $secret = self::generateRandomSecret();
        if(self::insertUser($conn, $username, $secret) === false)
        {
            return null;
        }
        return self::queryUserByUsername($conn, $username);
    }

    private static function generateRandomSecret()
    {
        $val = '';
        for($i = 0; $i < 256; $i++)
        {
            $val .= chr(rand(65, 90));
        }
        return $val;
    }

    /**
     * @param $conn mysqli
     * @param $username
     * @param $secret
     * @return bool
     */
    private static function insertUser($conn, $username, $secret)
    {
        self::cleanUsers($conn);
        $stmt = $conn->prepare("INSERT INTO home_users (username, secret) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $secret);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param $conn mysqli
     * @param $username
     * @return HomeUser|null
     */
    public static function queryUserByUsername($conn, $username)
    {
        $sql = "SELECT id, username, secret, google_registered FROM home_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new HomeUser($row["id"], $row["username"], $row["secret"], $row["google_registered"]);
        }

        return null;
    }

    /**
     * @param $conn mysqli
     * @param $id
     * @return HomeUser|null
     */
    public static function queryUserById($conn, $id)
    {
        $sql = "SELECT id, username, secret, google_registered FROM home_users WHERE id = $id";
        $result = $conn->query($sql);
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new HomeUser($row["id"], $row["username"], $row["secret"], $row["google_registered"]);
        }

        return null;
    }

    /**
     * @param $conn mysqli
     * @param $id
     * @return bool|mysqli_result
     */
    public static function enableUserById($conn, $id)
    {
        $sql = "UPDATE home_users SET enabled = 1 WHERE id = $id";
        return $conn->query($sql);
    }


    /**
     * @param $conn mysqli
     * @return bool|mysqli_result
     */
    public static function cleanUsers($conn)
    {
        $sql = "DELETE FROM home_users WHERE enabled = 0";
        return $conn->query($sql);
    }

    /**
     * @param $conn mysqli
     * @param int $id
     * @param bool $registered
     * @return bool|mysqli_result
     */
    public static function setGoogleRegistered(mysqli $conn, int $id, bool $registered)
    {
        $val = $registered ? 1 : 0;
        $sql = "UPDATE home_users SET google_registered = $val WHERE id = $id";
        return $conn->query($sql);
    }
}