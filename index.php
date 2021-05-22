<?php
    require_once("vendor/autoload.php");
    require_once("includes/config_includes.php");
    use Telegram\Bot\Api;
    use Api\Cowin;
    $bot = new Api('1755386616:AAFH3PIzoumgJn1nOEy-i_YV8evDUUWq0qk');
    $cowin = new Cowin();
    $startCmd = new Vendor\App\Commands\startCommand();
    $bot->addCommand($startCmd);
    $update = $bot->commandsHandler(true);
?>