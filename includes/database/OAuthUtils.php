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
    public static function insertAuthCode($conn, $client_id, $user_id, $scopes)
    {
        $code = base64_encode(random_bytes(64));
        $sql = "INSERT INTO oauth_tokens (token, client_id, user_id, scopes, type, expires) VALUES 
                ('$code', $client_id, $user_id, ?, 'auth_code', (NOW() + INTERVAL 5 MINUTE))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $scopes);
        if($stmt->execute() !== false)
        {
            return $code;
        }
        return null;
    }

    /**
     * @param $conn mysqli
     * @param $auth_code
     * @param $client_id
     * @return array|null
     */
    public static function exchangeCodeForTokens($conn, $auth_code, $client_id)
    {
        $sql = "SELECT user_id, scopes FROM oauth_tokens WHERE token='$auth_code' 
                AND type='auth_code' AND client_id=$client_id AND expires > NOW()";
        $result = $conn->query($sql);
        if($result->num_rows > 0)
        {
            $sql = "DELETE FROM oauth_tokens WHERE token='$auth_code'";
            $conn->query($sql);

            $row = $result->fetch_assoc();
            $user_id = $row["user_id"];
            $scopes =$row["scopes"];

            return OAuthUtils::generateAndInsertTokens($conn, $client_id, $user_id, $scopes);
        }

        return null;
    }

    private static function generateAndInsertTokens(mysqli $conn, int $client_id, string $user_id, string $scopes)
    {
        $access_token = base64_encode(random_bytes(64));
        $sql = "INSERT INTO oauth_tokens (token, client_id, user_id, scopes, type, expires) VALUES 
                    ('$access_token', $client_id, $user_id, ?, 'access_token', (NOW() + INTERVAL 30 DAY ))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $scopes);
        $stmt->execute();

        $refresh_token = base64_encode(random_bytes(64));
        $sql = "INSERT INTO oauth_tokens (token, client_id, user_id, scopes, type) VALUES 
                    ('$refresh_token', $client_id, $user_id, ?, 'refresh_token')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $scopes);
        $stmt->execute();

        return ["access_token" => $access_token, "refresh_token" => $refresh_token];
    }

    /**
     * @param $conn mysqli
     * @return bool|mysqli_result
     */
    public static function removeExpired($conn)
    {
        $sql = "DELETE FROM oauth_tokens WHERE expires < NOW()";
        return $conn->query($sql);
    }

    /**
     * @param $conn mysqli
     * @param $token
     * @return int
     */
    public static function getUserIdForToken($conn, $token)
    {
        $sql = "SELECT user_id FROM oauth_tokens WHERE token=? AND type='access_token'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($user_id);
        if($stmt->fetch())
            return $user_id;
        return null;
    }

    public static function exchangeRefreshForAccessToken(mysqli $conn, int $client_id, string $refresh_token)
    {
        $sql = "SELECT user_id, scopes FROM oauth_tokens WHERE token = ? AND type='refresh_token'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $refresh_token);
        $stmt->execute();
        $stmt->bind_result($user_id, $scopes);
        if(!$stmt->fetch())
            return null;

        return OAuthUtils::generateAndInsertTokens($conn, $client_id, $user_id, $scopes);
    }
}