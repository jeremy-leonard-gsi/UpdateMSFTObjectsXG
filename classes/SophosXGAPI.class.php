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
    
    public function __construct(Config $config) {
        $this->config = $config;
    }
    public function getFQDNHostGroup($name){
        $xml=sprintf('<Get><FQDNHostGroup><Name>%s</Name></FQDNHostGroup></Get>',$name);
        $domxpath = $this->doAPI($xml);

        foreach($domxpath->evaluate('//FQDNHostGroup') AS $FQDNHostGroup){
            if($FQDNHostGroup->getElementsByTagName('Name')[0]->nodeValue==$name){
                $obj = new stdClass();
                $obj->Name = $FQDNHostGroup->getElementsByTagName('Name')[0]->nodeValue;
                $obj->Description = $FQDNHostGroup->getElementsByTagName('Description')[0]->nodeValue;
                foreach($FQDNHostGroup->getElementsByTagName('FQDNHostList')[0]->getElementsByTagName('FQDNHost') AS $FQDNHosts){
                    $obj->FQDNHostList[]=$FQDNHosts->nodeValue;
                }
                return $obj;
            }
        }
    }    
    public function addFQDNHostGroup($fqdn, $name=null, $FQDNHostGroupList=[]){
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
    public function removeFQDNHostGroup($name) {
        $xml=sprintf('<Remove><FQDNHost><Name>%s</Name></FQDNHost></Remove>',$name);
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
                foreach($FQDNHostGroup->getElementsByTagName('FQDNList')[0]->getElementsByTagName('FQDNHost') AS $FQDNHosts){
                    $obj->FQDNGroupList[]=$FQDNGroups->nodeValue;
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
        $xml=sprintf('<Remove><FQDNHost><Name>%s</Name></FQDNHost></Remove>',$name);
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
        error_log(print_r(curl_getinfo($curl),true));
        curl_close($curl);
        error_log($resp);
//        error_log(print_r($resp,true));
        
        $dom = new DOMDocument();
        $dom->loadXML($resp);
        $domxpath = new DOMXPath($dom);
        
        return $domxpath;
    }
}