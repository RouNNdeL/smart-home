<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-23
 * Time: 16:15
 */

function tcp_send($string)
{
    error_reporting(0);
    $filename = __DIR__ . "/interface.dat";
    $filename_status = __DIR__ . "/status";
    $interface = explode(":", file_get_contents($filename));
    $fp = fsockopen($interface[0], $interface[1], $errno, $errstr, 0.1);
    error_reporting(E_ALL);
    if(!$fp)
    {
        if(file_exists($filename_status)) unlink($filename_status);
        return false;
    }
    else
    {
        file_put_contents($filename_status, "");
        if($string !== null) fwrite($fp, $string);
        fclose($fp);
        return true;
    }
}