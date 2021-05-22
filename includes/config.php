<?php
    spl_autoload_register(function($class){
        $pos= strripos($class,'\\')+1;
        $class_name = strtolower(substr($class,$pos));
        require_once (__DIR__."/class/{$class_name}.class.php");
    });
?>