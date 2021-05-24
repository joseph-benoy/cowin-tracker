<?php
try{
    apcu_add("x","value_XXXX",10000);
    echo apcu_fetch("x");
}
catch(Exception $e){
    echo $e->getMessage();
}
?>