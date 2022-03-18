<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include(__DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'autoloader.php');
include(__DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

$config = new Config($argv[1] ?? null);

$msft = new MSFTEndpoints($config);
if(($endpoints = $msft->checkForUpdates())!==false){
    $urls=[];
    $ips=[];
    foreach($endpoints as $endpoint){
        if(isset($endpoint->urls)){
            foreach($endpoint->urls as $url){
                $urls[]=$url;
            }
        }
        if(isset($endpoint->ips)){
            foreach($endpoint->ips as $ip){
                $ips[]=$ip;
            }
        }
    }    

    $xg = new SophosXGAPI($config);
    
    // Remove existing IPHosts

    $oldHosts = $xg->getIPHostGroup("Microsoft Endpoint IPs");
    if($oldHosts===false){
        echo "Creating Microsoft Endpoint IPs IPHostGroup\n";
        $xg->addIPHostGroup('Microsoft Endpoint IPs');
    }else{
        print_r($oldHosts);
        if(isset($oldHosts->HostList) AND is_array($oldHosts->HostList)){
            foreach($oldHosts->HostList as $oldHost){
                $xg->removeIPHost($oldHost);
            }
        }
    }
    
    // Create new IPHosts
    foreach($ips as $ip){
        $subnet = cidr2NetmaskAddr($ip);
        $address = substr($ip, 0, stripos($ip,'/'));
        $xg->addIPHost("MSFT-Endpoint-$ip", SophosXGAPI::HostType_Network, ['Microsoft Endpoint IPs'], $address, $subnet);
    }

    // Remove existing FQDNHosts
    
    $oldHosts = $xg->getFQDNHostGroup("Microsoft Endpoint FQDNs");
    if($oldHosts===false){
        echo "Creating Microsoft Endpoint FQDNs FQDNHostGroup\n";
        $xg->addFQDNHostGroup('Microsoft Endpoint FQDNs');
    }else{
        print_r($oldHosts);
        if(isset($oldHosts->FQDNHostList) AND is_array($oldHosts->FQDNHostList)){
            foreach($oldHosts->FQDNHostList as $oldHost){
                $xg->removeFQDNHost($oldHost);
            }
        }
    }
    
    // Create new IPHosts
    foreach($urls as $url){
        echo $url."\n";
        $xg->addFQDNHost($url, "MSFT-Endpoint-$url",  ['Microsoft Endpoint FQDNs']);
    }

    
    $config->write('localVersion',$msft->getCurrentVersion());
}

$config->save();

function cidr2NetmaskAddr ($cidr) {

    $ta = substr ($cidr, strpos ($cidr, '/') + 1) * 1;
    $netmask = str_split (str_pad (str_pad ('', $ta, '1'), 32, '0'), 8);

    foreach ($netmask as &$element)
      $element = bindec ($element);

    return join ('.', $netmask);

  }
