<?php
    namespace Telegram\Api;
    abstract class Command extends Action{
        protected $updateObj;
        protected $apiToken;
        public function __construct($apiToken,$updateObj){
            $this->updateObj = $updateObj;
            $this->apiToken = $apiToken;
        }
        abstract public function handle();
        public function getChatId():string{
            if(property_exists($this->updateObj,"message")){
                return $this->updateObj->message->chat->id;
            }
            else{
                return $this->updateObj->callback_query->from->id;
            }
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

        //reply functions


        protected function replyMessage($text,$replyMarkup=null){
            if($replyMarkup!=null){
                $data = array("chat_id"=>$this->getChatId(),"text"=>$text,"reply_markup"=>$replyMarkup);
                $this->sendReply('sendMessage',$data);
            }
            else{
                $data = array("chat_id"=>$this->getChatId(),"text"=>$text);
                $this->sendReply('sendMessage',$data);
            }
        }
        protected function replyPhoto($path,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$this->getChatId(),'caption'=>$caption,"reply_markup"=>$replyMarkup,"photo"=>new \CURLFile(realpath($path)));
                $this->sendReply('sendPhoto',$data);
            }
            else{
                $data = array("chat_id"=>$this->getChatId(),'caption'=>$caption,"photo"=>new \CURLFile(realpath($path)));
                $this->sendReply('sendPhoto',$data);
            }
        }
        protected function replyDoc($path,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$this->getChatId(),'caption'=>$caption,"reply_markup"=>$replyMarkup,"document"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendDocument",$data);
            }
            else{
                $data = array("chat_id"=>$this->getChatId(),"document"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendDocument",$data);
            }
        }
        protected function replyAudio($path,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$this->getChatId(),'caption'=>$caption,"reply_markup"=>$replyMarkup,"audio"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendAudio",$data);
            }
            else{
                $data = array("chat_id"=>$this->getChatId(),"audio"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendAudio",$data);
            }
        }
        protected function replyVoice($path,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$this->getChatId(),'caption'=>$caption,"reply_markup"=>$replyMarkup,"voice"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVoice",$data);
            }
            else{
                $data = array("chat_id"=>$this->getChatId(),"voice"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVoice",$data);
            }
        }
        protected function replyChatAction($action="typing"){
            $data = array("chat_id"=>$this->getChatId(),"action"=>$action);
            $this->sendReply("sendChatAction",$data);
        }
        protected function replyVideo($path,$thumb=null,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($thumb==null){
                $thumb = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$this->getChatId(),"thumb"=>$thumb,'caption'=>$caption,"reply_markup"=>$replyMarkup,"video"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVideo",$data);
            }
            else{
                $data = array("chat_id"=>$this->getChatId(),"thumb"=>$thumb,'caption'=>$caption,"video"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVideo",$data);
            }
        }

        //command Session handling functions
        protected function setCommandSession($sessionName){
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
        protected function deleteCommandSession(){
            apcu_delete($this->getChatId());
        }
    }
?>