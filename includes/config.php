<?php
    spl_autoload_register(function($class){
        $pos= strripos($class,'\\')+1;
        $class_name = strtolower(substr($class,$pos));
        require_once (__DIR__."/includes/class/{$class_name}.class.php");
    });
?>