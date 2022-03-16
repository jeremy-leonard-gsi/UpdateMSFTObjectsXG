<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MSFTEndpoints
 *
 * @author jeremyl
 */
class MSFTEndpoints {
    
    public function __construct(Config $config) {
        $this->config = $config;
    }
    public function getCurrentVersion() {
        $url = $this->config->msftURL.'/version/worldwide?clientrequestid='.$this->config->clientrequestid;
        return $version = json_decode(file_get_contents($url))->latest;
    }
    protected function getCurrentEndpoints(){
        $url = $this->config->msftURL.'/endpoints/worldwide?clientrequestid='.$this->config->clientrequestid.'&NoIPv6='.$this->config->NoIPv6;
        if($this->config->TenantName){
            $url .= '&TenantName='.$this->config->TenantName;
        }
        return json_decode(file_get_contents($url));
    }
    public function checkForUpdates(){
        if($this->getCurrentVersion() != $this->config->localVersion){
            return $this->getCurrentEndpoints();
        }else{
            return false;
        }
    }
}
