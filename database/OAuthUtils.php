<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 15:57
 */

class OAuthUtils
{
    /**
     * Only run this method on sanitized data (prepared statements are not in use)
     * @param $conn mysqli
     * @param $client_id
     * @param $user_id
     * @return null|string
     */
    public static function insertAuthCode($conn, $client_id, $user_id)
    {
        $code = base64_encode(random_bytes(64));
        $sql = "INSERT INTO oauth (token, client_id, user_id, type, expires) VALUES 
('$code', $client_id, $user_id, 'auth_code', (NOW() + INTERVAL 5 MINUTE))";
        if($conn->query($sql) !== false)
        {
            return $code;
        }
        return null;
    }

    /**
     * @param $conn mysqli
     * @return bool|mysqli_result
     */
    public static function removeExpired($conn)
    {
        $sql = "DELETE FROM oauth WHERE expires < NOW()";
        return $conn->query($sql);
    }
}