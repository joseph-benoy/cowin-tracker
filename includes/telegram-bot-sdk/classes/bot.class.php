<?php
    namespace Telegram\Api;
    class Bot extends Action{
        protected $classArray=[];
        protected $callbackQueryArray=[];
        public function __construct($token,$classArray=null){
            $this->apiToken = $token;
            if($classArray!=null){
                foreach($classArray as $class){
                    if(!in_array($class,$this->classArray)){
                        array_push($this->classArray,$class);
                    }
                }
            }
        }
        protected function isCommand($text){
            if(substr($text,0,1)=="/"){
                return true;
            }
            else{
                return false;
            }
        }
        protected function filterUpdate($updateObj):\stdClass{
            if(property_exists($updateObj,'callback_query')){
                    $filterObj = new \stdClass;
                    $filterObj->chatId = $updateObj->callback_query->from->id;
                    $filterObj->queryData =$updateObj->callback_query->data;
                    return $filterObj;
            }
            elseif($this->isCommand($updateObj->message->text)){
                $cmd = $this->getCommandName($updateObj->message->text);
                if($cmd!=null){
                    $filterObj = new \stdClass;
                    $filterObj->chatId = $updateObj->message->chat->id;
                    $filterObj->commandName = $cmd;
                    return $filterObj;
                }
                else{
                    $filterObj = new \stdClass;
                    $filterObj->chatId = $updateObj->message->chat->id;
                    $filterObj->commandName = "";
                    return $filterObj;
                }
            }
            else{
                $filterObj = new \StdClass;
                $filterObj->chatId = $updateObj->message->chat->id;
                $filterObj->text = $updateObj->message->text;
                return $filterObj;
            }
        }
        protected function getCommandName($text){
            $data = ltrim($text,"/");
            if(in_array($data,$this->classArray)){
                return $data;
            }
            else{
                return null;
            }
        }
        public function capture():void{
            $updateObj = json_decode(file_get_contents("php://input"));
            $filterObj = $this->filterUpdate($updateObj);
            if(property_exists($filterObj,'queryData')){
                //execute as callback_query
                if($filterObj->queryData==""){
                    //$this->sendError($filterObj,"Invalid callback query");
                }
                else{
                    $this->executeCallbackQuery($filterObj,$updateObj);
                }
            }
            elseif(property_exists($filterObj,'commandName')){
                //execute as registered command
                if($filterObj->commandName==""){
//                    $this->sendError($filterObj,"Invalid Command");// send error message because command is not registered
                }
                else{
                    $this->executeCommand($filterObj,$updateObj);
                }
            }
            else{
                //execute as random input
                $this->routeRandomInput($filterObj,$updateObj);
            }
        }
        protected function executeCommand($filterObj,$updateObj):void{
            if(!apcu_exists($filterObj->chatId)){
                $class = $filterObj->commandName;
                $command = new $class($this->apiToken,$updateObj);
                $command->handle();
            }
            else{
                error_log("@@@@@@@@@@@@@@@@@",0);
//                $this->sendError($filterObj,"Invalid input");
            }
        }
        public function registerCommands($class_array){
            foreach($class_array as $class){
                if(!in_array($class,$this->classArray)){
                    array_push($this->classArray,$class);
                }
            }
        }
        protected function getCommandSession($chatId){
            if(apcu_exists($chatId)){
                return apcu_fetch($chatId);
            }else{
                return "";
            }
        }
        protected function executeCallbackQuery($filterObj,$updateObj):void{
            $commandSessionObj = json_decode($this->getCommandSession($filterObj->chatId));
            if($commandSessionObj!=""){
                $class = $commandSessionObj->commandName;
                $command = new $class($this->apiToken,$updateObj);
                $command->handle(null,$commandSessionObj,$filterObj->queryData);
            }
        }
        protected function routeRandomInput($filterObj,$updateObj){
            $commandSessionObj = json_decode($this->getCommandSession($filterObj->chatId));
            if($commandSessionObj!=""){
                $class = $commandSessionObj->commandName;
                $command = new $class($this->apiToken,$updateObj);
                $command->handle($filterObj->text,$commandSessionObj,null);
            }
        }
    }

?> 