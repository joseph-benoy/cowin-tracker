<?php
    class start extends  Telegram\Api\Command{
        public function handle($randomData=null,$commandSessionObj=null,$queryData=null){
            if($commandSessionObj==null){
                $this->replyMessage("<b>Welcome Cowin Tracker!</b>We will keep updated with the availability of vaccine session within your district or pincode area<em>Please don't spam with random inputs</em><u>Choose appropriate option from the list</u>","HTML",null);
            }
        }
    }
?>