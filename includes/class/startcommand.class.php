<?php
    namespace Vendor\App\Commands;

    use Telegram\Bot\Actions;
    use Telegram\Bot\Commands\Command;
    class startCommand extends Command{
        protected $name = "start";
        protected $description = "Start command to get started";
        public function handle(){
            $this->replyWithMessage(['text'=>'Hello! welcome to Cowin vaccine tracker. Here are our available commands : ']);
            $this->replyWithChatAction(['action'=>Actions::TYPING]);
            $commands = $this->getTelegram()->getCommands();
            $response = "";
            foreach($commands as $name=>$command){
                $response.=sprintf("/%s - %s".PHP_EOL,$name,$command->getDescription());
            }
            $this->replyWithMessage(['text'=>$response]);
            $this->triggerCommand('subscribe');
        }
    }
?>