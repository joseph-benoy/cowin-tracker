<?php
    require_once("vendor/autoload.php");
    require_once("includes/config.php");
    use Telegram\Bot\Api;
    use Api\Cowin;
    use Vendor\App\Commands\startCommand;
    $bot = new Api('1755386616:AAFH3PIzoumgJn1nOEy-i_YV8evDUUWq0qk');
    $cowin = new Cowin();
    $startCmd = new startCommand();
    $bot->addCommand($startCmd);
    $update = $bot->commandsHandler(true);
?>