<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-27
 * Time: 18:12
 */

require_once (__DIR__."/../secure.php");
require_once (__DIR__."/../Logging.php");

$entity_id = "e016d879-78c8-4592-97a7-9e8f3732d4d4";

function rename_entity($old, $new)
{
    delete($old);
    put($new);
}

function put($name)
{
    global $dialogflow_dev_token;
    global $entity_id;

    $ch = curl_init("https://api.dialogflow.com/v1/entities/$entity_id/entries");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: $dialogflow_dev_token"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("value" => $name)));

    curl_exec($ch);
}

function delete($name)
{
    global $dialogflow_dev_token;
    global $entity_id;

    $ch = curl_init("https://api.dialogflow.com/v1/entities/$entity_id/entries");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: $dialogflow_dev_token"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array($name)));

    curl_exec($ch);
}