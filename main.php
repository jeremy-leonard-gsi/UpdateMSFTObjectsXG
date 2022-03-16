<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include(__DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'autoloader.php');
include(__DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

$config = new Config();

$msft = new MSFTEndpoints($config);
$endpoints = $msft->checkForUpdates();
$urls=[];
$ips=[];
foreach($endpoints as $endpoint){
    foreach($endpoint->urls as $url){
        $urls[]=$url;
    }
    foreach($endpoint->ips as $ip){
        $ips[]=$ip;
    }
}
print_r(array_unique($urls));
print_r(array_unique($ips));

#$xg = new SophosXGAPI($db);

//$config->write('localVersion',$msft->getCurrentVersion());
$config->save();