<?php
    namespace Telegram\component;
    class ReplyKeyboard{
        public $oneTimeKeyboard = true;
        public $resizeKeyboard = true;
        public $keyboard = array();
        public function addRow($row){
            array_push($this->keyboard,$row);
        }
        public function getMarkup(){
            return json_encode(
                array(
                    "keyboard"=>$this->keyboard,
                    "resize_keyboard"=>$this->resizeKeyboard,
                    "one_time_keyboard"=>$this->oneTimeKeyboard
                )
            );
        }
    }
?>