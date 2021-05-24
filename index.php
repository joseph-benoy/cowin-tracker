<?php
try{
    apc_add("x","value_XXXX",10000);
    echo apc_fetch("x");
catch(Exception $e){
    echo $e->getMessage();
}
?>