<?php
/**
 * [Basic api class]
 */
    class Cowin{
        /**
         * Return the list of states in India where vaccines are available
         * 
         * @return [array]
         * @throws exception when returns json error object
         */
        function get_states(){
            try{
                $curl = curl_init("https://cdn-api.co-vin.in/api/v2/admin/location/states");
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                $agents = array(
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                    'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                    'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                    'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1'
                 
                );
                curl_setopt($curl,CURLOPT_USERAGENT,$agents[array_rand($agents)]);
                return json_decode(curl_exec($curl),true)['states'];
            }
            catch(\Exception $error){
                error_log("get_states error : {$error->getMessage()}",0);
            }
        }
        /**
         * returns the list of districts in the state
         * @param mixed $state_name
         * 
         * @return [array]
         */
        function get_districts($state_name){
            try{
                $state_id = null;
                foreach($this->get_states() as $state){
                    if($state_name==$state['state_name']){
                        $state_id = $state['state_id'];
                    }
                }
                if($state_id==null){
                    return false;
                }
                else{
                    $curl = curl_init("https://cdn-api.co-vin.in/api/v2/admin/location/districts/{$state_id}");
                    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                    $agents = array(
                        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                        'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                        'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1'
                     
                    );
                    curl_setopt($curl,CURLOPT_USERAGENT,$agents[array_rand($agents)]);
                    return json_decode(curl_exec($curl),true)['districts'];
                }
            }
            catch(Exception $error){
                error_log("get_districts error : {$error->getMessage()}",0);
            }
        }
        /**
         * returns all the sessions on a particular date in a pincode
         * @param mixed $pincode
         * @param mixed $date
         * 
         * @return [array]
         */
        function get_sessions_by_pin($pincode,$date)
        {
            try{
                $curl = curl_init("https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/findByPin?pincode={$pincode}&date={$date}");
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                $agents = array(
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                    'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                    'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                    'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1'
                 
                );
                curl_setopt($curl,CURLOPT_USERAGENT,$agents[array_rand($agents)]);
                $response = json_decode(curl_exec($curl),true);
                if(array_key_exists('errorCode',$response)){
                    throw new \Exception("{$response['error']}");
                }
                $sessions = $response['sessions'];
                return $sessions;
            }
            catch(\Exception $error){
                error_log("get_pincode error : {$error->getMessage()}",0);
            }
        }
        /**
         * returns all the sessions on a date in a district
         * @param mixed $state
         * @param mixed $district
         * @param mixed $date
         * 
         * @return [array]
         */
        function get_sessions_by_district($state,$district,$date){
            try{
                $district_id = null;
                foreach($this->get_districts($state) as $dist){
                    if($district==$dist['district_name']){
                        $district_id = $dist['district_id'];
                    }
                }
                if($district_id==null){
                    return false;
                }
                else{
                    $curl = curl_init("https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/findByDistrict?district_id={$district_id}&date={$date}");
                    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                    $agents = array(
                        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                        'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                        'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1'
                     
                    );
                    curl_setopt($curl,CURLOPT_USERAGENT,$agents[array_rand($agents)]);
                    $response = json_decode(curl_exec($curl),true);
                    if(array_key_exists('errorCode',$response)){
                        throw new \Exception("{$response['error']}");
                    }
                    $sessions = $response['sessions'];
                    return $sessions;
                }
            }
            catch(\Exception $error){
                error_log("get_slot_by_district error : {$error->getMessage()}",0);
            }
        }
        /**
         * returns all the centers and session available for a week from the day specified in a pincode
         * @param mixed $pincode
         * @param mixed $date
         * 
         * @return [array]
         */
        function get_calender_by_pin($pincode,$date){
            try{
                $curl = curl_init("https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/calendarByPin?pincode={$pincode}&date={$date}");
                curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                $agents = array(
                    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                    'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                    'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                    'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1'
                 
                );
                curl_setopt($curl,CURLOPT_USERAGENT,$agents[array_rand($agents)]);
                $response = json_decode(curl_exec($curl),true);
                if(array_key_exists('errorCode',$response)){
                    throw new \Exception("{$response['error']}");
                }
                return $response['centers'];
            }
            catch(\Exception $error){
                error_log("get calender by pin error : {$error->getMessage()}",0);
            }
        }
        /**
         * returns all the centers and session available for a week from the day specified in a district
         * @param mixed $state
         * @param mixed $district
         * @param mixed $date
         * 
         * @return [type]
         */
        function get_calender_by_district($state,$district,$date){
            try{
                $district_id = null;
                $districts = $this->get_districts($state);
                $x = json_encode($districts,JSON_PRETTY_PRINT);
                foreach($districts as $dist){
                    if($district==$dist['district_name']){
                        $district_id = $dist['district_id'];
                    }
                }
                if($district_id==null){
                    return false;
                }
                else{
                    $curl = curl_init("https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/calendarByDistrict?district_id={$district_id}&date={$date}");
                    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                    $agents = array(
                        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                        'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                        'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1'
                     
                    );
                    curl_setopt($curl,CURLOPT_USERAGENT,$agents[array_rand($agents)]);
                    $response = json_decode(curl_exec($curl),true);
                    if(array_key_exists('errorCode',$response)){
                        throw new \Exception("{$response['error']}");
                    }
                    return $response['centers'];
                }
            }
            catch(\Exception $error){
                error_log("get calender by pin error : {$error->getMessage()}",0);
            }
        }
    }
?>