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
    echo "Microsoft has a newer version. Updating objects...\n";
    $config->write('localVersion',$msft->getCurrentVersion());
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
    
    $ips = array_unique($ips);
    $urls = array_unique($urls);
    
    $xg = new SophosXGAPI($config);
    
    // Remove existing IPHosts
    echo "Processing IPs...\n";
    $oldHosts = $xg->getIPHostGroup("Microsoft Endpoint IPs");
    if($oldHosts===false){
        echo "Creating Microsoft Endpoint IPs IPHostGroup\n";
        $xg->addIPHostGroup('Microsoft Endpoint IPs');
    }else{
        echo "Clearing existing IP objects...\n";
        if(isset($oldHosts->HostList) AND is_array($oldHosts->HostList)){
            foreach($oldHosts->HostList as $oldHost){
                echo "\t$oldHost\n";
                $xg->removeIPHost($oldHost);
            }
        }
    }
    
    // Create new IPHosts
    echo "Creating new IP hosts...\n";
    foreach($ips as $ip){
        echo "\t$ip\n";
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
        echo "Clearing existing FQDN objects...\n";
        if(isset($oldHosts->FQDNHostList) AND is_array($oldHosts->FQDNHostList)){
            foreach($oldHosts->FQDNHostList as $oldHost){
                echo "\t$oldHost\n";
                $xg->removeFQDNHost($oldHost);
            }
        }
    }
    
    // Create new FQDNHosts
    echo "Creating new FQDN hosts...\n";
    foreach($urls as $url){
        echo "\t$url\n";
        $xg->addFQDNHost($url, "MSFT-Endpoint-$url",  ['Microsoft Endpoint FQDNs']);
    }

    
}

$config->save();

function cidr2NetmaskAddr ($cidr) {

    $ta = substr ($cidr, strpos ($cidr, '/') + 1) * 1;
    $netmask = str_split (str_pad (str_pad ('', $ta, '1'), 32, '0'), 8);

    foreach ($netmask as &$element)
      $element = bindec ($element);

    return join ('.', $netmask);

  }
