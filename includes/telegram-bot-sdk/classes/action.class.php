<?php
    namespace Telegram\Api;
    class Action{
        protected $apiToken;
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
        public function sendMessage($chatId,$text,$replyMarkup=null){
            if($replyMarkup!=null){
                $data = array("chat_id"=>$chatId,"text"=>$text,"reply_markup"=>$replyMarkup);
                $this->sendReply('sendMessage',$data);
            }
            else{
                $data = array("chat_id"=>$chatId,"text"=>$text);
                $this->sendReply('sendMessage',$data);
            }
        }
        public function sendPhoto($chatId,$text,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$chatId,'caption'=>$caption,"reply_markup"=>$replyMarkup,"photo"=>new \CURLFile(realpath($path)));
                $this->sendReply('sendPhoto',$data);
            }
            else{
                $data = array("chat_id"=>$chatId,'caption'=>$caption,"photo"=>new \CURLFile(realpath($path)));
                $this->sendReply('sendPhoto',$data);
            }
        }
        public function sendVideo($chatId,$path,$thumb=null,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($thumb==null){
                $thumb = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$chatId,"thumb"=>$thumb,'caption'=>$caption,"reply_markup"=>$replyMarkup,"video"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVideo",$data);
            }
            else{
                $data = array("chat_id"=>$chatId,"thumb"=>$thumb,'caption'=>$caption,"video"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVideo",$data);
            }
        }
        public function sendDoc($chatId,$path,$caption=null,$replyMarkup=null){
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
        public function sentChatAction($chatId,$action="typing"){
            $data = array("chat_id"=>$chatId,"action"=>$action);
            $this->sendReply("sendChatAction",$data);
        }
        public function sendVoice($chatId,$path,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$chatId,'caption'=>$caption,"reply_markup"=>$replyMarkup,"voice"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVoice",$data);
            }
            else{
                $data = array("chat_id"=>$chatId,"voice"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendVoice",$data);
            }
        }
        public function sendAudio($chatId,$path,$caption=null,$replyMarkup=null){
            if($caption==null){
                $caption = "";
            }
            if($replyMarkup!=null){
                $data = array("chat_id"=>$chatId,'caption'=>$caption,"reply_markup"=>$replyMarkup,"audio"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendAudio",$data);
            }
            else{
                $data = array("chat_id"=>$chatId,"audio"=>new \CURLFile(realpath($path)));
                $this->sendReply("sendAudio",$data);
            }
        }
    }
?>