<?php
    class Bot{
        public $apiToken;
        public $updateObj;
        public $cowin;
        public function __construct($token){
            $this->apiToken = $token;
            $this->cowin = new Cowin();
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
            return $this->updateObj->message->chat->id;
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
            return  $this->updateObj->message->from->first_name." ".$this->updateObj->message->from->last_name;
        }
        protected function isCommand($text){
            if(substr($text,0,1)=="/"){
                return true;
            }
            else{
                return false;
            }
        }
        /////////////////////////////////////////
        public function capture():void{
            $this->updateObj = json_decode(file_get_contents("php://input"));
            if($this->isCommand($this->getText())){
                if($this->getText()=="/start"){
                    $this->startCommand();
                }
            }
            elseif($this->getText()=="Back to main menu"){
                $this->deleteCommandSession();
                $this->mainMenu();
            }
            elseif($this->getText()=="List sessions by District"){
                $this->listByDistrict();
            }
            elseif($this->getText()=="List sessions by Pincode"){
                $this->listByPincode();
            }
            elseif($this->getText()=="Add Pincode to watchlist"){
                $this->addPinWatch();
            }
            else{
                if(apcu_exists($this->getChatId())){
                    $sessionObj = json_decode(apcu_fetch($this->getChatId()));
                    $methodName = $sessionObj->methodName;
                    $this->$methodName($sessionObj);
                }
                else{
                    $backBtn = new ReplyKeyboard();
                    $backBtn->addRow([["text"=>"Back to main menu","callback_data"=>"Back to main menu"]]);
                    $this->replyMessage("*Invalid request!*\nPlease try again.","markdown",$backBtn->getMarkup());
                }
            }
        }
        public function replyMessage($text,$parse_mode=null,$replyMarkup=null){
            $result = "";
            if($parse_mode!=null){
                if($replyMarkup!=null){
                    $data = array("chat_id"=>$this->getChatId(),"text"=>$text,"parse_mode"=>$parse_mode,"reply_markup"=>$replyMarkup);
                    $result = $this->sendReply('sendMessage',$data);
                }
                else{
                    $data = array("chat_id"=>$this->getChatId(),"text"=>$text,"parse_mode"=>$parse_mode);
                    $result = $this->sendReply('sendMessage',$data);
                }
            }
            else{
                if($replyMarkup!=null){
                    $data = array("chat_id"=>$this->getChatId(),"text"=>$text,"reply_markup"=>$replyMarkup);
                    $result = $this->sendReply('sendMessage',$data);
                }
                else{
                    $data = array("chat_id"=>$this->getChatId(),"text"=>$text);
                    $result = $this->sendReply('sendMessage',$data);
                }
            }
            return $result;
        }
        public function setCommandSession($methodName,$sessionName){
            $obj = new \stdClass;
            $obj->methodName = $methodName;
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
            $keyboardMarkup = new ReplyKeyboard();
            $keyboardMarkup->addRow(array(array("text"=>"List sessions by District","callback_data"=>"List sessions by District")));
            $keyboardMarkup->addRow([["text"=>"List sessions by Pincode","callback_data"=>"List sessions by Pincode"]]);
            $keyboardMarkup->addRow([["text"=>"Add Pincode to watchlist","callback_data"=>"Add Pincode to watchlist"]]);
            $result = $this->replyMessage("*Hello {$this->getFullname()}!*\nWelcome to Cowin Tracker! We will help you updated with the availability of vaccine sessions within your district or your pincode area.\n\n*Choose appropriate option from the menu*\n_Please don't spam with random inputs_","markdown",$keyboardMarkup->getMarkup());
            error_log("##start command executed{$result}",0);
        }
        public function mainMenu(){
            $keyboardMarkup = new ReplyKeyboard();
            $keyboardMarkup->addRow(array(array("text"=>"List sessions by District","callback_data"=>"List sessions by District")));
            $keyboardMarkup->addRow([["text"=>"List sessions by Pincode","callback_data"=>"List sessions by Pincode"]]);
            $keyboardMarkup->addRow([["text"=>"Add Pincode to watchlist","callback_data"=>"Add Pincode to watchlist"]]);
            $result = $this->replyMessage("*Main menu*","markdown",$keyboardMarkup->getMarkup());
            error_log("##Back to main menu{$result}",0);
        }
        public function listByDistrict($sessionObj=null){
            if($sessionObj==null){
                $statesList = [];
                if(apcu_exists($this->getChatId()."statesList")){
                    $statesList = json_decode(apcu_fetch($this->getChatId()."statesList"),true);
                }
                else{
                    $statesList = $this->cowin->get_states();
                    apcu_add($this->getChatId()."statesList",json_encode($statesList));
                }
                $listStateKeyboard = new ReplyKeyboard();
                foreach($statesList as $state){
                    $listStateKeyboard->addRow([["text"=>"{$state['state_name']}","callback_data"=>"{$state['state_name']}"]]);
                }
                $listStateKeyboard->addRow([["text"=>"Back to main menu","callback_data"=>"Back to main menu"]]);
                $result = $this->replyMessage("*Choose your state*","markdown",$listStateKeyboard->getMarkup());
                $this->setCommandSession("listByDistrict","getStateName");
            }
            elseif($sessionObj->sessionName=="getStateName"){
                $stateName = $this->getText();
                if(!apcu_exists($this->getChatId()."stateName")){
                    apcu_add($this->getChatId()."stateName",$stateName);
                }
                else{
                    apcu_store($this->getChatId()."stateName",$stateName);
                }
                $data = $this->cowin->get_districts($stateName);
                $listDistrictKeyboard = new ReplyKeyboard();
                foreach($data as $district){
                    $listDistrictKeyboard->addRow([["text"=>"{$district['district_name']}","callback_data"=>"{$district['district_name']}"]]);
                }
                $listDistrictKeyboard->addRow([["text"=>"Back to main menu","callback_data"=>"Back to main menu"]]);
                $result = $this->replyMessage("*Choose your District*","markdown",$listDistrictKeyboard->getMarkup());
                $this->setCommandSession("listByDistrict","getDistrictName");
            }
            elseif($sessionObj->sessionName=="getDistrictName"){
                $stateName = "";
                $backBtn = new ReplyKeyboard();
                $backBtn->addRow([["text"=>"Back to main menu","callback_data"=>"Back to main menu"]]);
                if(apcu_exists($this->getChatId()."stateName")){
                    $stateName = apcu_fetch($this->getChatId()."stateName");
                    $districtName = $this->getText();
                    $data = $this->cowin->get_calender_by_district($stateName,$districtName,date("d-m-Y"));
                    if(count($data)<0){
                        $this->replyMessage("*Unfortunately no vaccine sesssions are available in this pincode area.*","markdown",null);
                    }
                    else{
                        foreach($data as $center){
                            $message = "*Center name* : {$center['name']}\n*Address : *{$center['address']}\n*Fee type : *{$center['fee_type']}\n";
                            $sessionMessage = "";
                            $slots = "";
                            foreach($center['sessions'] as $session){
                                $sessionMessage .= "\n*Date : *{$session['date']}\n*Available capacity : *{$session['available_capacity']}\n*Minimum Age limit : *{$session['min_age_limit']}\n*Vaccine : *{$session['vaccine']}\n*Available capacity of dose 1 : *{$session['available_capacity_dose1']}\n*Available capacity of dose 2 : *{$session['available_capacity_dose2']}\n\n*Slots : *\n";
                                foreach($session['slots'] as $slot){
                                    $slots .= "     {$slot}\n";
                                }
                                $sessionMessage.=$slots;
                                $slots = "";
                            }
                            $message.=$sessionMessage;
                            $result = $this->replyMessage($message,"markdown",null);
                        }
                    }
                    $result = $this->replyMessage("*List finished!*\n*Please note that the information listed above is totally retrived from the official COWIN API. For more details visit official cowin website*\n_Go back to main menu for more options_","markdown",$backBtn->getMarkup());
                    $this->deleteCommandSession();
                }
                else{
                    $this->replyMessage("*Invalid request!*\n*Please try again.*","markdown",$backBtn->getMarkup());
                    $this->deleteCommandSession();
                }
            }
            error_log("##State list created {$result}",0);
        }
        public function listByPincode($sessionObj=null){
            if($sessionObj==null){
                $result = $this->replyMessage("*Enter the pincode to list the vaccine sessions*","markdown",null);
                $this->setCommandSession("listByPincode","getPincode");
            }
            elseif($sessionObj->sessionName=="getPincode"){
                $pin = $this->getText();
                $backBtn = new ReplyKeyboard();
                $backBtn->addRow([["text"=>"Back to main menu","callback_data"=>"Back to main menu"]]);
                if(is_numeric($pin)&&strlen($pin)==6){
                    $result = "";
                    $data = $this->cowin->get_calender_by_pin($this->getText(),date("d-m-Y"));
                    if(count($data)===0){
                        $this->replyMessage("*Unfortunately no vaccine sesssions are available in this pincode area.*","markdown",null);
                    }
                    else{
                        foreach($data as $center){
                            $message = "*Center name* : {$center['name']}\n*Address : *{$center['address']}\n*Fee type : *{$center['fee_type']}\n";
                            $sessionMessage = "";
                            $slots = "";
                            foreach($center['sessions'] as $session){
                                $sessionMessage .= "\n*Date : *{$session['date']}\n*Available capacity : *{$session['available_capacity']}\n*Minimum Age limit : *{$session['min_age_limit']}\n*Vaccine : *{$session['vaccine']}\n*Available capacity of dose 1 : *{$session['available_capacity_dose1']}\n*Available capacity of dose 2 : *{$session['available_capacity_dose2']}\n\n*Slots : *\n";
                                foreach($session['slots'] as $slot){
                                    $slots .= "     {$slot}\n";
                                }
                                $sessionMessage.=$slots;
                                $slots = "";
                            }
                            $message.=$sessionMessage;
                            $result = $this->replyMessage($message,"markdown",null);
                        }
                    }
                    $result = $this->replyMessage("*List finished!*\n*Please note that the information listed above is totally retrived from the official COWIN API. For more details visit official cowin website*\n_Go back to main menu for more options_","markdown",$backBtn->getMarkup());
                    $this->deleteCommandSession();
                }
                else{
                    //send invalid pincode
                    $this->replyMessage("*Invalid pincode!*\nPlease try again.","markdown",$backBtn->getMarkup());
                    $this->deleteCommandSession();
                }
            }
            error_log("##Enter pincode message sent {$result}",0);
        }
        public function addPinWatch($sessionObj=null){
            $result = "";
            if($sessionObj==null){
                $result = $this->replyMessage("*Enter the pincode to list the vaccine sessions*","markdown",null);
                $this->setCommandSession("addPinWatch","getPincode");
            }
            elseif($sessionObj->sessionName=="getPincode"){
                $pin = $this->getText();
                $backBtn = new ReplyKeyboard();
                $backBtn->addRow([["text"=>"Back to main menu","callback_data"=>"Back to main menu"]]);
                if(is_numeric($pin)&&strlen($pin)==6){
                    $result = "";
                    $connection = new PDO("mysql:host=localhost;dbname=cowin_tracker", "joseph", "3057");
                    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $statement = $connection->prepare("INSERT INTO PIN_WATCHLIST VALUES(:chatId,:pin)");
                    $chatId = $this->getChatId();
                    $statement->bindParam(":chatId",$chatId);
                    $statement->bindParam(":pin",$pin);
                    $statement->execute();
                    $result = $this->replyMessage("*PIN {$pin} is added to the watch list!*\nWe will update you whenever there are vaccination sessions avaialable in {$pin}.\n_Go back to main menu for more options_","markdown",$backBtn->getMarkup());
                    $this->deleteCommandSession();
                }
                else{
                    //send invalid pincode
                    $this->replyMessage("*Invalid pincode!*\nPlease try again.","markdown",$backBtn->getMarkup());
                    $this->deleteCommandSession();
                }
            }
            error_log("##Enter pincode message sent {$result}",0);
        }
    }
?> 