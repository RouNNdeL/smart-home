<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-22
 * Time: 15:22
 */

class OAuthServiceUtils
{
    const REDIRECT_URI = "https://home.zdul.xyz/oauth/response.php";

    /**
     * @param mysqli $conn
     * @param string $state
     * @return bool
     */
    public static function insertState(mysqli $conn, string $state)
    {
        $sql = "INSERT INTO service_auth_states (state) VALUES(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $state);
        return $stmt->execute();
    }

    /**
     * @param mysqli $conn
     * @param string $state
     * @return bool
     */
    public static function checkState(mysqli $conn, string $state)
    {
        $sql = "SELECT state FROM service_auth_states WHERE state = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $state);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            $sql = "DELETE FROM service_auth_states WHERE state = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $state);
            $stmt->execute();
            return true;
        }
        $stmt->close();
        return false;
    }
}