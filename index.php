<?php
    require_once("includes/classes/cowin.class.php");
    require_once("includes/telegram-bot-sdk/autoload.php");
    class start extends Telegram\Api\Command{
        public function handle($randomData=null,$commandSessionObj=null,$queryData=null){
            $cowinObj = new Api\Cowin();
            $statesList = [];
            $backBtnMarkup = new Telegram\component\ReplyKeyboard()->addRow([["text"=>"Back to main menu","callback_data"=>"Back to main menu"]])->getMarkup();
            if(apcu_exists("state_list")){
                $statesList = json_decode(apcu_fetch("state_list"),true);
            }
            else{
                $statesList = $cowinObj->get_states();
                apcu_add("state_list",json_encode($statesList));
            }
            $districtList = [];
            if($commandSessionObj==null){
                $keyboardMarkup = new Telegram\component\ReplyKeyboard();
                $keyboardMarkup->addRow([["text"=>"List sessions by District","callback_data"=>"List sessions by District"]]);
                $keyboardMarkup->addRow([["text"=>"List sessions by Pincode","callback_data"=>"List sessions by Pincode"]]);
                $keyboardMarkup->addRow([["text"=>"Add District to watchlist","callback_data"=>"Add district to watchlist"]]);
                $keyboardMarkup->addRow([["text"=>"Add Pincode to watchlist","callback_data"=>"Add Pincode to watchlist"]]);
                $result = $this->replyMessage("*Welcome to Cowin Tracker!*\n\nWe will help you updated with the availability of vaccine sessions within your district or your pincode area.\n\n*Choose appropriate option from the menu*\n_Please don't spam with random inputs_","markdown",$keyboardMarkup->getMarkup());
                $this->setCommandSession("startSession");
            }
            if($commandSessionObj->sessionName=="startSession"){
                if($randomData==="List sessions by District"){
                    $listStateKeyboard = new Telegram\component\ReplyKeyboard();
                    foreach($statesList as $state){
                        $listStateKeyboard->addRow([["text"=>"{$state['state_name']}","callback_data"=>"{$state['state_name']}"]]);
                    }
                    $result = $this->replyMessage("*Choose your state*","markdown",$listStateKeyboard->getMarkup());
                    $this->setCommandSession("inputStateSession");
                }
                if($randomData==="List sessions by Pincode"){
                    $result = $this->replyMessage("*Enter the pincode for which you want to list the sessions*","markdown",null);
                    $this->setCommandSession("inputPincodeSession");
                }
            }
            if($commandSessionObj->sessionName=="inputPincodeSession"){
                if(is_numeric($randomData)&&strlen($randomData)==6){
                    $result = "";
                    $data = $cowinObj->get_calender_by_pin($randomData,date("d-m-Y"));
                    $x = json_encode($data,JSON_PRETTY_PRINT);
                    error_log("~~~~~~~~{$x}",0);
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
                    $this->replyMessage("*List finished!*\n*Go back to main menu for more options*","markdown",null);
                    error_log("%%%%%%%%%%%%%% {$result}",0);
                }
                else{
                    //send invalid pincode
                }
            }
            if($commandSessionObj->sessionName=="inputStateSession"){
                if($randomData!=null){
                    $stateExists = false;
                    foreach($statesList as $state){
                        if($state['state_name']==$randomData){
                            $stateExists = true;
                        }
                    }
                    if($stateExists){
                        error_log("@@@@@@@@@@@@@@@@@@@@@",0);
                        if(apcu_exists($this->getChatId()."-state")){
                            if(apcu_fetch($this->getChatId()."-state")!=$randomData){
                                apcu_store($this->getChatId()."-state",$randomData);
                            }
                        }
                        else{
                            apcu_add($this->getChatId()."-state",$randomData);
                        }
                        $listDistrictKeyboard = new Telegram\component\ReplyKeyboard();
                        $districtList = $cowinObj->get_districts($randomData);
                        foreach($districtList as $district){
                            $listDistrictKeyboard->addRow([["text"=>"{$district['district_name']}","callback_data"=>"{$district['district_name']}"]]);
                        }
                        $result = $this->replyMessage("*Choose your District*","markdown",$listDistrictKeyboard->getMarkup());
                        $this->setCommandSession("inputDistrictSession");
                    }
                    else{
                        error_log("@@@@@@@@@@@@@########State does not exits#######@@@@@@@@",0);
                        //send error
                    }
                }
            }
            if($commandSessionObj->sessionName=="inputDistrictSession"){
                if($randomData!=null){
                    $result = "";
                    $state = "";
                    if(apcu_exists($this->getChatId())){
                        $state = apcu_fetch($this->getChatId()."-state");
                    }
                    $data = $cowinObj->get_calender_by_district($state,$randomData,date("d-m-Y"));
                    $x = json_encode($data,JSON_PRETTY_PRINT);
                    error_log(">>>>>>>>>>>>>>>>> {$x}",0);
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
                    error_log("%%%%%%%%%%%%%% {$result}",0);
                }
            }
        }
    }
    $bot = new Telegram\Api\Bot("1755386616:AAFH3PIzoumgJn1nOEy-i_YV8evDUUWq0qk");
    $bot->registerCommands(['start']);
    $bot->capture();
?>