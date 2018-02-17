<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 15:17
 */

class ApiClient
{
    public $id;
    public $name;
    public $secret;

    /**
     * ApiClient constructor.
     * @param $id
     * @param $name
     * @param $secret
     */
    private function __construct($id, $name, $secret)
    {
        $this->id = $id;
        $this->name = $name;
        $this->secret = $secret;
    }

    /**
     * @param $conn mysqli
     * @param $id
     * @return ApiClient|null
     */
    public static function queryClientById($conn, $id)
    {
        $sql = "SELECT id, name, secret FROM api_clients WHERE id=$id";
        $result = $conn->query($sql);

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return new ApiClient($row["id"], $row["name"], $row["secret"]);
        }

        return null;
    }
}