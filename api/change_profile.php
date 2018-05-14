<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-01-02
 * Time: 16:37
 */

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    require_once(__DIR__ . "/../web/includes/Data.php");
    require_once(__DIR__ . "/../network/tcp.php");
    $data = Data::getInstance();
    $n_profile = (int) file_get_contents("php://input");
    if($data->getProfile($n_profile) !== false)
    {
        $data->setCurrentProfile($n_profile);
        Data::save();
        tcp_send($data->globalsToJson());
        http_response_code(204);
    }
    else
    {
        http_response_code(400);
    }
}
else
{
    http_response_code(400);
}