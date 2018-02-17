<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 11:05
 */

require_once __DIR__ . "/../secure_config.php";

class DbUtils
{
    /** @var mysqli */
    private static $connection = null;

    /**
     * @return mysqli
     */
    public static function getConnection()
    {
        global $db_username;
        global $db_passwd;

        if(self::$connection === null)
        {
            return self::$connection = new mysqli("localhost", $db_username, $db_passwd, "smart_home");
        }
        else
        {
            return self::$connection;
        }
    }

    /**
     * @param $conn mysqli
     * @param $user_id
     * @param $success
     * @return bool
     */
    public static function insertLoginAttempt($conn, $user_id, $success)
    {
        $sql = "INSERT INTO home_login_attempts (user_id, successfull, time) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $s = $success ? 1 : 0;
        $stmt->bind_param("ii", $user_id, $s);
        $result =  $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * @param $conn mysqli
     * @param $user_id
     * @param $seconds
     * @return int
     */
    public static function countFailedLoginAttempts($conn, $user_id, $seconds)
    {
        $sql = "SELECT id FROM home_login_attempts WHERE successfull=0 AND user_id=$user_id AND time>(NOW() - INTERVAL $seconds SECOND)";
        $result = $conn->query($sql);
        return $result->num_rows;
    }
}