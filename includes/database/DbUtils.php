<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 11:05
 */

require_once __DIR__ . "/../../secure_config.php";

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
}