<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 15:57
 */

class OAuthUtils
{
    const SCOPE_HOME_CONTROL = "home_control";
    const SCOPE_ADVANCED_CONTROL = "advanced_control";

    const SUPPORTED_SCOPES = [OAuthUtils::SCOPE_HOME_CONTROL];

    /**
     * Only run this method on sanitized data (prepared statements are not in use)
     * @param $conn mysqli
     * @param $client_id
     * @param $user_id
     * @param $scopes
     * @return null|string
     * @throws InvalidArgumentException
     */
    public static function insertAuthCode(mysqli $conn, string $client_id, int $user_id, string $scopes)
    {
        if(!OAuthUtils::checkScopes($scopes))
            throw new InvalidArgumentException("Invalid scope");
        try
        {
            $code = base64_encode(openssl_random_pseudo_bytes(128));
        }
        catch(Exception $exception)
        {
            return null;
        }
        $sql = "INSERT INTO oauth_tokens (token, client_id, user_id, scopes, type, expires) VALUES 
                ('$code', $client_id, $user_id, ?, 'auth_code', (NOW() + INTERVAL 5 MINUTE))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $scopes);
        if($stmt->execute() !== false)
        {
            $stmt->close();
            return $code;
        }
        $stmt->close();
        return null;
    }

    /**
     * @param $conn mysqli
     * @param $auth_code
     * @param $client_id
     * @return array|null
     */
    public static function exchangeCodeForTokens(mysqli $conn, string $auth_code, string $client_id)
    {
        $sql = "SELECT user_id, scopes FROM oauth_tokens WHERE token='$auth_code' 
                AND type='auth_code' AND client_id=? AND expires > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if($result->num_rows > 0)
        {
            $sql = "DELETE FROM oauth_tokens WHERE token='$auth_code'";
            $conn->query($sql);

            $row = $result->fetch_assoc();
            $user_id = $row["user_id"];
            $scopes = $row["scopes"];

            return OAuthUtils::generateAndInsertTokens($conn, $client_id, $user_id, $scopes);
        }

        return null;
    }

    private static function generateAndInsertTokens(mysqli $conn, string $client_id,
                                                    string $user_id, string $scopes, bool $only_access = false)
    {
        $access_token = base64_encode(openssl_random_pseudo_bytes(128));
        $sql = "INSERT INTO oauth_tokens (token, client_id, user_id, scopes, type, expires) VALUES 
                    ('$access_token', ?, $user_id, ?, 'access_token', (NOW() + INTERVAL 30 DAY ))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $client_id, $scopes);
        $stmt->execute();
        $stmt->close();

        if(!$only_access)
        {
            $refresh_token = base64_encode(openssl_random_pseudo_bytes(128));
            $sql = "INSERT INTO oauth_tokens (token, client_id, user_id, scopes, type) VALUES 
                    ('$refresh_token', ?, $user_id, ?, 'refresh_token')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $client_id, $scopes);
            $stmt->execute();
            $stmt->close();

            return ["access_token" => $access_token, "refresh_token" => $refresh_token];
        }
        return  ["access_token" => $access_token];
    }

    /**
     * @param $conn mysqli
     * @param $token
     * @return int
     */
    public static function getUserIdForToken($conn, $token)
    {
        $sql = "SELECT user_id FROM oauth_tokens WHERE token=? AND type='access_token' AND expires > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($user_id);
        if($stmt->fetch())
        {
            $stmt->close();
            return $user_id;
        }
        $stmt->close();
        return null;
    }

    /**
     * @param $conn mysqli
     * @param $token
     * @return string
     */
    public static function getScopesForToken(mysqli $conn, string $token)
    {
        $sql = "SELECT scopes FROM oauth_tokens WHERE token = ? AND type = 'access_token' AND expires > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($scopes);
        if($stmt->fetch())
        {
            $stmt->close();
            return $scopes;
        }
        $stmt->close();
        return null;
    }

    public static function exchangeRefreshForAccessToken(mysqli $conn, string $client_id, string $refresh_token)
    {
        $sql = "SELECT user_id, scopes FROM oauth_tokens WHERE token = ? AND type='refresh_token' AND client_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $refresh_token, $client_id);
        $stmt->execute();
        $stmt->bind_result($user_id, $scopes);
        if(!$stmt->fetch())
            return null;

        $stmt->close();
        return OAuthUtils::generateAndInsertTokens($conn, $client_id, $user_id, $scopes, true);
    }

    /**
     * @param string $scopes
     * @return bool
     */
    public static function checkScopes(string $scopes)
    {
        $arr = explode(" ", $scopes);

        foreach($arr as $item)
        {
            if(!in_array($item, OAuthUtils::SUPPORTED_SCOPES))
            {
                return false;
            }
        }

        return true;
    }
}