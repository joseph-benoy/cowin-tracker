<?php
    require_once("includes/autoload.php");
    function sendFlag($centerArray,$center){
        $obj = new \stdClass;
        $obj->availablity = 0;
        $obj->insert = false;
        foreach($center['sessions'] as $session){
            if($session['available_capacity']!=0){
                $obj->availablity += $session['available_capacity'];
            }
        }
        if(count($centerArray)==0){
            $obj->insert = true;
            return $obj;
        }
        if(!in_array($center['center_id'],array_column($centerArray,"centerId"))){
            $obj->insert = true;
            return $obj;
        }
        else{
            foreach($centerArray as $centerObj){
                if($centerObj['centerId']==$center['center_id']&&$centerObj['availability']!=$obj->availablity){
                    return $obj;
                }
            }
            $obj->availablity = 0;
            return $obj;
        }
    }
    //bot object created
    $bot = new Bot("1755386616:AAFH3PIzoumgJn1nOEy-i_YV8evDUUWq0qk");
    //cowin api object
    $cowin = new Cowin();
    //initiating db connection
    $connection = new PDO("mysql:host=localhost;dbname=cowin_tracker", "joseph", "3057");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //reading data from pincode watchlist
    $statement = $connection->prepare("SELECT * FROM PIN_WATCHLIST");
    $statement->execute();
    $pinChatArray = $statement->fetchAll(PDO::FETCH_ASSOC);

    //reading data from center list;
    $statement = $connection->prepare("SELECT * FROM CENTER_LIST");
    $statement->execute();
    $centerArray = $statement->fetchAll(PDO::FETCH_ASSOC);

    //selecting distinct pincode
    $pinList = array_unique(array_column($pinChatArray,"pin"));
    //looping through each distinct pincode
    foreach($pinList as $pin){
        //getting vaccine data from api
        $vaccineData = $cowin->get_calender_by_pin($pin,date("d-m-Y"));
        //loopin over each center
        foreach($vaccineData as $center){

            //check whether availability changed in a center
            $flagObj = sendFlag($centerArray,$center);
            //if changed
            if($flagObj->availablity!=0){
                //message construction for sending
                $message = "*VACCINE UPDATE!*\n\n*Center name* : {$center['name']}\n*Address : *{$center['address']}\n*Fee type : *{$center['fee_type']}\n";
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
                //gettting chat id list with the pincode
                $chatIdList = [];
                foreach($pinChatArray as $pinChat){
                    if($pinChat['pin']==$pin){
                        array_push($chatIdList,$pinChat['chatId']);
                    }
                }

                //sending message to each chat id
                foreach($chatIdList as $chatId){
                    $result = $bot->sendVaccineUpdate($chatId,$message,"markdown",null);
                }
                $availablity = $flagObj->availablity;

                //if center is not added in the list then insert else update the existing center list
                if($flagObj->insert){
                    $centerId = $center['center_id'];
                    $statement = $connection->prepare("INSERT INTO CENTER_LIST VALUES(:centerId,:pin,:availability)");
                    $statement->bindParam(":centerId",$centerId);
                    $statement->bindParam(":pin",$pin);
                    $statement->bindParam(":availability",$availability);
                    $statement->execute();
                }
                else{
                    $centerId = $center['center_id'];
                    $statement = $connection->prepare("UPDATE CENTER_LIST SET availability=:availability WHERE centerId=:centerId");
                    $statement->bindParam(":availability",$availablity);
                    $statement->bindParam(":centerId",$centerId);
                    $statement->execute();
                }
            }
        }
    }
?>