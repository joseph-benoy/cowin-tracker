<?php
    spl_autoload_register(function($class){
        $pos= strripos($class,'\\')+1;
        $class_name = strtolower(substr($class,$pos));
        $file_name = __DIR__."/classes/{$class_name}.class.php";
        if(file_exists($file_name)){
            require_once ($file_name);
        }
    });
?>