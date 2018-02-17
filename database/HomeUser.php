<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-16
 * Time: 19:35
 */

class HomeUser
{
    private $id;
    private $username;
    private $secret;

    /**
     * HomeUser constructor.
     * @param $id
     * @param $username
     * @param $secret
     */
    private function __construct($id, $username, $secret)
    {
        $this->id = $id;
        $this->username = $username;
        $this->secret = $secret;
    }

    public static function newUser($conn, $username)
    {

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
     */
    private static function insertUser($conn, $username, $secret)
    {
        $stmt = $conn->prepare("INSERT INTO home_users (username, secret) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $secret);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param $conn mysqli
     * @param $username
     * @return HomeUser|null
     */
    private static function queryUserByUsername($conn, $username)
    {
        $sql = "SELECT id, username, secret FROM home_users WHERE username = $username";
        $result = $conn->query($sql);
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new HomeUser($row["id"], $row["username"], $row["secret"]);
        }

        return null;
    }
}