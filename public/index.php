<?php
/**
 * Created by OxGroup.
 * User: aliaxander
 * Date: 18.05.15
 * Time: 10:34
 */

ini_set("allow_url_fopen", true);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Moscow');
header('Content-type: text/html; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
$allowHeaders = "X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding";
header('Access-Control-Allow-Headers: ' . $allowHeaders);
header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Origin: *');
$loader = require __DIR__ . '/../vendor/autoload.php';
require(__DIR__ . "/../config.php");
require(__DIR__ . "/../OxApp/Routes.php");


//$account = file('1.txt');
//foreach ($account as $row) {
//    $row=str_replace("\n",'', $row);
//    $file = file_get_contents('https://www.instagram.com/' . $row . '/?__a=1');
//    $account=json_decode($file);
//    PopularAccounts::add(['account' => $account->user->id]);
//}

//$api=new FbWeb();
//print_r($api->create());
//
////$userAgent = new RandomUserAgent();
////echo $userAgent->random_uagent();