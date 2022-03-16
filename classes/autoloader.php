<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

spl_autoload_register(function ($class_name) {
    include __DIR__ . DIRECTORY_SEPARATOR . $class_name . '.class.php';
});