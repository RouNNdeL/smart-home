<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 19:56
 */
require_once(__DIR__ . "/../includes/Data.php");
require_once(__DIR__ . "/../includes/Utils.php");
require_once(__DIR__ . "/../../network/tcp.php");

$data = Data::getInstance();
$name = Utils::getInstance()->getString("default_profile_name");
$i = $data->getMaxIndex() + 1;
$name = str_replace("\$n", $i + 1, $name);
$profile = new Profile($name);
$overflow = $data->addProfile($profile);
if($overflow !== false)
{
    Data::save();
    $avr_index = $data->getAvrIndex($i);
    if($avr_index !== false)
    {
        tcp_send($data->globalsToJson());
        tcp_send($profile->toSend($avr_index));
    }
    header("Location: /profile/" . $i);
}
else
{
    include(__DIR__ . "/../error/404.php");
}