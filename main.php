<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include(__DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'autoloader.php');
include(__DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

$config = new Config($argv[1] ?? null);

//$msft = new MSFTEndpoints($config);
//$endpoints = $msft->checkForUpdates();
//$urls=[];
//$ips=[];
//foreach($endpoints as $endpoint){
//    foreach($endpoint->urls as $url){
//        $urls[]=$url;
//    }
//    foreach($endpoint->ips as $ip){
//        $ips[]=$ip;
//    }
//}
//print_r(array_unique($urls));
//print_r(array_unique($ips));



$xg = new SophosXGAPI($config);

//$xg->addFQDNHost('*.1test.org','*.1test.net',['Microsoft Services']);

//echo json_encode($xg->getFQDNHostGroup('Microsoft Services'),JSON_PRETTY_PRINT);
//echo json_encode($xg->getFQDNHost('*.1test.net'),JSON_PRETTY_PRINT);

//$xg->addFQDNHostGroup('1-Test','Testing',[]);
//print_r($xg->addFQDNHost('*.1test.net','*.1test.net',['1-Test']));
//$xg->removeFQDNHost('*.1test.net');
//$xg->removeFQDNHostGroup('1-Test');


//$xg->removeFQDNHostGroup('1-Test');

//$xg->removeFQDNHost('*.1test.net');

//print_r($xg->addIPHostGroup('Test Networks', 'Testing'));

//print_r($xg->addIPHost('1TestHost', SophosXGAPI::HostType_IP, ['Test Networks'], '10.1.1.1'));
//print_r($xg->addIPHost('1TestHost', SophosXGAPI::HostType_Network, [], '10.1.1.0','255.255.255.0'));
print_r($xg->removeIPHost('1TestHost'));

//print_r($xg->getIPHostGroup('Test Networks'));

//$xg->removeIPHostGroup('Test Networks');

//$config->write('localVersion',$msft->getCurrentVersion());
$config->save();