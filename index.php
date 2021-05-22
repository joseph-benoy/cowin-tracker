<?php
    require_once("vendor/autoload.php");
    require_once("includes/config.php");
    use Telegram\Bot\Api;
    use Api\Cowin;
    $bot = new Api('1755386616:AAFH3PIzoumgJn1nOEy-i_YV8evDUUWq0qk');
    $cowin = new Cowin();
    print_r($cowin->get_states());
?>