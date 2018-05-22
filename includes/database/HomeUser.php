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

    public static function newUser(mysqli $conn, string $username, string $password)
    {
        $password_hash = self::hashPassword($password);
        if(self::insertUser($conn, $username, $password_hash) === false)
        {
            return null;
        }
        return self::queryUserByUsername($conn, $username);
    }

    public static function authenticateUser(mysqli $conn, string $username, string $password)
    {
        $sql = "SELECT password FROM home_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->bind_result($password_hash );
        $stmt->execute();
        if(!$stmt->fetch() || !self::verifyPassword($password, $password_hash))
        {
            $stmt->close();
            return null;
        }
        $stmt->close();

        return static::queryUserByUsername($conn, $username);
    }

    private static function hashPassword(string $password): string
    {
        $options = ["cost" => 12];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    private static function verifyPassword(string $password, string $hash): string
    {
        return password_verify($password, $hash);
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
        $sql = "SELECT id, username, secret_2fa, google_registered FROM home_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new HomeUser($row["id"], $row["username"], $row["secret_2fa"], $row["google_registered"]);
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
        $sql = "SELECT id, username, secret_2fa, google_registered FROM home_users WHERE id = $id";
        $result = $conn->query($sql);
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new HomeUser($row["id"], $row["username"], $row["secret_2fa"], $row["google_registered"]);
        }

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
    public static function setGoogleRegistered(mysqli $conn, int $id, bool $registered)
    {
        $val = $registered ? 1 : 0;
        $sql = "UPDATE home_users SET google_registered = $val WHERE id = $id";
        return $conn->query($sql);
    }

    /**
     * @param mysqli $conn
     * @return HomeUser[]
     */
    public static function queryAllRegistered(mysqli $conn)
    {
        $sql = "SELECT id, username, secret_2fa FROM home_users WHERE google_registered = 1";
        $result = $conn->query($sql);
        $arr = [];

        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                $arr[] = new HomeUser($row["id"], $row["username"], $row["secret_2fa"], true);
            }
        }

        return $arr;
    }
}