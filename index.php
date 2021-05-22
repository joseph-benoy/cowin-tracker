<?php
    require_once("vendor/autoload.php");
    use Telegram\Bot\Api;
    $bot = new Api('1755386616:AAFH3PIzoumgJn1nOEy-i_YV8evDUUWq0qk');
    $me= $bot->getMe();
    print_r($me);
?>