<?php
//    require_once("includes/autoload.php");
    require_once("includes/telegram-bot-sdk/autoload.php");
    class start extends Telegram\Api\Command{
        public function handle($randomData=null,$commandSessionObj=null,$queryData=null){
            if($commandSessionObj==null){
                $keyboardMarkup = new Telegram\component\ReplyKeyboard();
                $keyboardMarkup->addRow([["text"=>"List sessions by District","callback_data"=>"List sessions by District"]]);
                $keyboardMarkup->addRow([["text"=>"List sessions by Pincode","callback_data"=>"List sessions by Pincode"]]);
                $keyboardMarkup->addRow([["text"=>"Add District to watchlist","callback_data"=>"Add district to watchlist"]]);
                $keyboardMarkup->addRow([["text"=>"Add Pincode to watchlist","callback_data"=>"Add Pincode to watchlist"]]);
                $result = $this->replyMessage("*Welcome to Cowin Tracker!*\n\nWe will help you updated with the availability of vaccine sessions within your district or your pincode area.\n\n*Choose appropriate option from the menu*\n_Please don't spam with random inputs_","markdown",$keyboardMarkup->getMarkup());
                $this->setSession("startSession");
                if($commandSessionObj->sessionName=="startSession"){
                    if($randomData==="List sessions by District"){
                        $listStateKeyboard = new Telegram\component\ReplyKeyboard();
                        $this->replyMessage();
                    }
                }
            }
        }
    }
    $bot = new Telegram\Api\Bot("1755386616:AAFH3PIzoumgJn1nOEy-i_YV8evDUUWq0qk");
    $bot->registerCommands(['start']);
    $bot->capture();
?>