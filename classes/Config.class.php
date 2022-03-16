<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Config
 *
 * @author jeremyl
 */
class Config {
    
    public function __construct() {
        $this->load();
        try{            
            if($this->clientrequestid===false){
                $this->genUUID();
            }
            if($this->msftURL===false){
                $this->write('msftURL','https://endpoints.office.com/endpoints/worldwide');
            }
            if($this->XGURL===false){
                throw new Exception("The config file is missing an entry for XGURL. This is the URL used to access the XG/XGS API.");
            }
            if($this->XGUser===false){
                throw new Exception("The config file is missing an entry for XGUser. This is the API user used to access the XG/XGS API.");
            }
            if($this->XGPassword===false){
                throw new Exception("The config file is missing an entry for XGPassword. This is the password for the API user used to access the XG/XGS API.");
            }
        }catch (Exception $ex){
            error_log($ex->getMessage());
            exit;
        }
    }
    public function __set($name, $value) {
        return $this->$name=$value;
    }
    public function __get($name) {
        return $this->$name ?? false;
    }
    public function read($name){
        return $this->$name ?? false;
    }
    public function write($name, $value){
        return $this->$name = $value;
    }
    public function load(){
        if(($json_data = file_get_contents(dirname(__DIR__).DIRECTORY_SEPARATOR.'config.json'))!==false){
            error_log("json file opened successfully.");
            $config = json_decode($json_data);
            error_log("Converted json data to object");
            print_r($config);
            foreach($config as $name => $value){
                $this->write($name, $value);
            }
        }else{
            $this->genUUID();
            $this->save();
        }
    }
    public function save(){
        file_put_contents(dirname(__DIR__).DIRECTORY_SEPARATOR.'config.json', json_encode($this, JSON_PRETTY_PRINT));
    }
    protected function genUUID(){
            return $this->clientrequestid = Ramsey\Uuid\Uuid::uuid4()->toString();        
    }
}
