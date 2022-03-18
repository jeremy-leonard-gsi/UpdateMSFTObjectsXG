<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SophosXGAPI
 *
 * @author jeremyl
 */
class SophosXGAPI {
    
    const IPFamily_IPv4='IPv4';
    const IPFamily_IPv6='IPv6';
    const HostType_IP='IP';
    const HostType_IPRange='IPRange';
    const HostType_IPList='IPList';
    const HostType_Network='Network';

    public function __construct(Config $config) {
        $this->config = $config;
    }
 
    public function getIPHost($name){
        $xml=sprintf('<Get><IPHost><Name>%s</Name></IPHost></Get>',$name);
        $domxpath = $this->doAPI($xml);

        foreach($domxpath->evaluate('//IPHost') AS $IPHost){
            $attrs = [
                'Name',
                'IPFamily',
                'HostType',
                'IPAddress',
                'Subnet',
                'StartIPAddress',
                'EndIPAddress',
                'ListOfIPAddresses'
            ];
            if($IPHost->getElementsByTagName('Name')[0]->nodeValue==$name){
                $obj = new stdClass();
                foreach($attrs as $attr){
                    $obj->$attr = $IPHost->getElementsByTagName($attr)[0]->nodeValue ?? null;
                }
                if(count($IPHost->getElementsByTagName('HostGroupList')) > 0){
                    foreach($IPHost->getElementsByTagName('HostGroupList')[0]->getElementsByTagName('HostGroup') AS $HostGroup){
                        $obj->HostGroupList[]=$HostGroup->nodeValue;
                    }
                }
                return $obj;
            }
        }
        
    }
    public function addIPHost($name, $HostType, $HostGroup=[],$IPAddress, $Subnet=null, $StartIPAddress=null, $EndIPAddress=null, $ListOfIPAddresses=[], $IPFamily=SophosXGAPI::IPFamily_IPv4) {
        if(count($HostGroup) > 0){
            $groups = '<HostGroupList>';
            foreach($HostGroup as $group){
                $groups .= sprintf('<HostGroup>%s</HostGroup>',$group);
            }
            $groups .= '</HostGroupList>';
        }else{
            $groups = '';
        }
        switch ($HostType) {
            case SophosXGAPI::HostType_IP:
                $xml = sprintf('<Set><IPHost><Name>%s</Name><IPFamily>%s</IPFamily><HostType>IP</HostType><IPAddress>%s</IPAddress>%s</IPHost></Set>',$name,$IPFamily,$IPAddress,$groups);
                break;
            case SophosXGAPI::HostType_IPList:
                $xml = sprintf('<Set><IPHost><Name>%s</Name><IPFamily>%s</IPFamily><HostType>IPList</HostType><ListOfIPAddresses>%s</ListOfIPAddresses>%s</IPHost></Set>',$name,$IPFamily,$ListOfIPAddresses,$groups);
                break;
            case SophosXGAPI::HostType_IPRange:
                $xml = sprintf('<Set><IPHost><Name>%s</Name><IPFamily>%s</IPFamily><HostType>IPRange</HostType><StartIPAddress>%s</StartIPAddress><EndIPAddress>%s</EndIPAddress>%s</IPHost></Set>',$name,$IPFamily,$StartIPAddress,$EndIPAddress,$groups);
                break;
            case SophosXGAPI::HostType_Network:
                $xml = sprintf('<Set><IPHost><Name>%s</Name><IPFamily>%s</IPFamily><HostType>Network</HostType><IPAddress>%s</IPAddress><Subnet>%s</Subnet>%s</IPHost></Set>',$name,$IPFamily,$IPAddress,$Subnet,$groups);
                break;
        }
        return $this->doAPI($xml);
    }
    public function removeIPHost($name){
        $IPHost = $this->getIPHost($name);
        $this->addIPHost(
                $name,
                $IPHost->HostType,
                [],
                $IPHost->IPAddress,
                $IPHost->Subnet,
                $IPHost->StartIPAddress,
                $IPHost->EndIPAddress,
                $IPHost->ListOfIPAddresses,
                $IPHost->IPFamily
                );
        $xml=sprintf('<Remove><IPHost><Name>%s</Name></IPHost></Remove>',$name);
        $this->doAPI($xml);
    }
        
    public function getFQDNHost($name){
        $xml=sprintf('<Get><FQDNHost><Name>%s</Name></FQDNHost></Get>',$name);
        $domxpath = $this->doAPI($xml);

        foreach($domxpath->evaluate('//FQDNHost') AS $FQDNHost){
            if($FQDNHost->getElementsByTagName('Name')[0]->nodeValue==$name){
                $obj = new stdClass();
                $obj->Name = $FQDNHost->getElementsByTagName('Name')[0]->nodeValue;
                $obj->FQDN = $FQDNHost->getElementsByTagName('FQDN')[0]->nodeValue;
                foreach($FQDNHost->getElementsByTagName('FQDNHostGroupList')[0]->getElementsByTagName('FQDNHostGroup') AS $FQDNHostGroup){
                    $obj->FQDNHostGroupList[]=$FQDNHostGroup->nodeValue;
                }

                return $obj;
            }
        }
    }    
    public function addFQDNHost($fqdn, $name=null, $FQDNHostGroupList=[]){
        $name = $name ?? $fqdn;
        if(count($FQDNHostGroupList) > 0){
            $groups = "<FQDNHostGroupList>";
            foreach($FQDNHostGroupList as $group){
                $groups .= sprintf('<FQDNHostGroup>%s</FQDNHostGroup>',$group);
            }
            $groups .= "</FQDNHostGroupList>";
        }else{
            $groups = '';
        }
        $xml=sprintf('<Set><FQDNHost><Name>%s</Name><FQDN>%s</FQDN>%s</FQDNHost></Set>',$name,$fqdn,$groups);
        $this->doAPI($xml);
    }
    public function removeFQDNHost($name) {
        $FQDNHost = $this->getFQDNHost($name);
        $this->addFQDNHost($FQDNHost->FQDN, $FQDNHost->Name, []);
        $xml=sprintf('<Remove><FQDNHost><Name>%s</Name></FQDNHost></Remove>',$name);
        $this->doAPI($xml);
    }
    
    public function getIPHostGroup($name){
        $xml=sprintf('<Get><IPHostGroup><Name>%s</Name></IPHostGroup></Get>',$name);
        if(($domxpath = $this->doAPI($xml))===false){
            return false;
        }else{
            foreach($domxpath->evaluate('//IPHostGroup') AS $IPHostGroup){
                if($IPHostGroup->getElementsByTagName('Name')[0]->nodeValue==$name){
                    $obj = new stdClass();
                    $obj->Name = $IPHostGroup->getElementsByTagName('Name')[0]->nodeValue;
                    $obj->IPFamily = $IPHostGroup->getElementsByTagName('IPFamily')[0]->nodeValue;
                    $obj->Description = $IPHostGroup->getElementsByTagName('Description')[0]->nodeValue;
                    if(count($IPHostGroup->getElementsByTagName('HostList'))>0){
                        foreach($IPHostGroup->getElementsByTagName('HostList')[0]->getElementsByTagName('Host') AS $Host){
                            $obj->HostList[]=$Host->nodeValue;
                        }
                    }
                    return $obj;
                }
                return false;
            }
        }
    }    
    public function addIPHostGroup($name, $description=null, $HostList=[],$IPFamily= SophosXGAPI::IPFamily_IPv4){
        if(count($HostList) > 0){
            $hosts = "<HostList>";
            foreach($HostList as $host){
                $hosts .= sprintf('<Host>%s</Host>',$host);
            }
            $hosts .= "</HostList>";
        }else{
            $hosts = '';
        }
        $xml=sprintf('<Set><IPHostGroup><Name>%s</Name><IPFamily>%s</IPFamily><Description>%s</Description>%s</IPHostGroup></Set>',$name,$IPFamily,$description,$hosts);
        $this->doAPI($xml);
    }
    public function removeIPHostGroup($name) {
        $xml=sprintf('<Remove><IPHostGroup><Name>%s</Name></IPHostGroup></Remove>',$name);
        $this->doAPI($xml);
    }

    public function getFQDNHostGroup($name){
        $xml=sprintf('<Get><FQDNHostGroup><Name>%s</Name></FQDNHostGroup></Get>',$name);
        if(($domxpath = $this->doAPI($xml))===false){
            return false;
        }else{
            foreach($domxpath->evaluate('//FQDNHostGroup') AS $FQDNHostGroup){
                if($FQDNHostGroup->getElementsByTagName('Name')[0]->nodeValue==$name){
                    $obj = new stdClass();
                    $obj->Name = $FQDNHostGroup->getElementsByTagName('Name')[0]->nodeValue;
                    $obj->Description = $FQDNHostGroup->getElementsByTagName('Description')[0]->nodeValue;
                    if(count($FQDNHostGroup->getElementsByTagName('FQDNHostList')) > 0){
                        foreach($FQDNHostGroup->getElementsByTagName('FQDNHostList')[0]->getElementsByTagName('FQDNHost') AS $FQDNHosts){
                            $obj->FQDNHostList[]=$FQDNHosts->nodeValue;
                        }
                    }
                    return $obj;
                }
            }
            return false;
        }
    }    
    public function addFQDNHostGroup($name, $description=null, $FQDNHostList=[]){
        if(count($FQDNHostList) > 0){
            $hosts = "<FQDNHostList>";
            foreach($FQDNHostList as $host){
                $hosts .= sprintf('<FQDNHost>%s</FQDNHost>',$host);
            }
            $hosts .= "</FQDNHostList>";
        }else{
            $hosts = '';
        }
        $xml=sprintf('<Set><FQDNHostGroup><Name>%s</Name><Description>%s</Description>%s</FQDNHostGroup></Set>',$name,$description,$hosts);
        $this->doAPI($xml);
    }
    public function removeFQDNHostGroup($name) {
        $xml=sprintf('<Remove><FQDNHostGroup><Name>%s</Name></FQDNHostGroup></Remove>',$name);
        $this->doAPI($xml);
    }
    
    protected function doAPI($xml){
        $request = 'reqxml=<?xml version="1.0" encoding="UTF-8"?><Request APIVersion="1805.1">';
        $request .= sprintf('<Login><Username>%s</Username><Password passwordform="encrypt">%s</Password></Login>',$this->config->XGUser,$this->config->XGPassword);
        $request .= $xml;
        $request .='</Request>';
        
        $url = 'https://'.$this->config->XGURL.':4444/webconsole/APIController';

        $curl = curl_init($url);
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_HEADER, true);
        
        $headers = [
           "Content-Type: application/x-www-form-urlencoded",
        ];

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = $request;

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

//        error_log(print_r(curl_getinfo($curl),true));

        $resp = curl_exec($curl);
//        error_log(print_r(curl_getinfo($curl),true));
        curl_close($curl);
        error_log(print_r($resp,true));
        
        if(strlen($resp)==0){
            return false;
        }else{
            $dom = new DOMDocument();
            $dom->loadXML($resp);
            $domxpath = new DOMXPath($dom);
            return $domxpath;
        }        
    }
}