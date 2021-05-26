<?php
    namespace Telegram\Api;
    class Bot{
        public $apiToken;
        public $updateObj;
        public function __construct($token){
            $this->apiToken = $token;
        }
        public function sendReply($method,$data){
            $url = "https://api.telegram.org/bot".$this->apiToken. "/" . $method;
            $curl = curl_init();
            if($method!='sendMessage'){
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Content-Type:multipart/form-data"
                ));
            }
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $url); 
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }
        public function getChatId():string{
            if(property_exists($this->updateObj,"message")){
                return $this->updateObj->message->chat->id;
            }
            else{
                return $this->updateObj->callback_query->from->id;
            }
        }
        public function replyChatAction($action="typing"){
            $data = array("chat_id"=>$this->getChatId(),"action"=>$action);
            $this->sendReply("sendChatAction",$data);
        }
        public function getText(){
            return $this->updateObj->message->text;
        }
        public function getUsername(){
            return $this->updateObj->from->username;
        }
        public function getFullname(){
            return  $this->updateObj->from->first_name." ".$this->updateObj->from->last_name;
        }
        protected function isCommand($text){
            if(substr($text,0,1)=="/"){
                return true;
            }
            else{
                return false;
            }
        }
        public function capture():void{
            $this->updateObj = json_decode(file_get_contents("php://input"));
            if($this->isCommand($this->getText())){
                if($this->getText()==="/start"){
                    $this->startCommand();
                }
                else{
                    error_log("##Not start command",0);
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
        public function replyMessage($text,$replyMarkup=null){
            if($replyMarkup!=null){
                $data = array("chat_id"=>$this->getChatId(),"text"=>$text,"reply_markup"=>$replyMarkup);
                $this->sendReply('sendMessage',$data);
            }
            else{
                $data = array("chat_id"=>$this->getChatId(),"text"=>$text);
                $this->sendReply('sendMessage',$data);
            }
        }
        public function setCommandSession($sessionName){
            $obj = new \stdClass;
            $obj->commandName = get_class($this);
            $obj->sessionName = $sessionName;
            if(!apcu_exists($this->getChatId())){
                apcu_add($this->getChatId(),json_encode($obj));
            }
            else{
                apcu_store($this->getChatId(),json_encode($obj));
            }
        }
        public function deleteCommandSession(){
            apcu_delete($this->getChatId());
        }
        public function startCommand(){
            $keyboardMarkup = new Telegram\component\ReplyKeyboard();
            $keyboardMarkup->addRow([["text"=>"List sessions by District","callback_data"=>"List sessions by District"]]);
            $keyboardMarkup->addRow([["text"=>"List sessions by Pincode","callback_data"=>"List sessions by Pincode"]]);
            $keyboardMarkup->addRow([["text"=>"Add District to watchlist","callback_data"=>"Add district to watchlist"]]);
            $keyboardMarkup->addRow([["text"=>"Add Pincode to watchlist","callback_data"=>"Add Pincode to watchlist"]]);
            $result = $this->replyMessage("*Hello {$this->getFullname()}!*Welcome to Cowin Tracker! We will help you updated with the availability of vaccine sessions within your district or your pincode area.\n\n*Choose appropriate option from the menu*\n_Please don't spam with random inputs_","markdown",$keyboardMarkup->getMarkup());
            error_log("##start command executed{$result}",0);
        }
    }
?> 