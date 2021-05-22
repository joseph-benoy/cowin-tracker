<?php
    namespace Vendor\App\Commands;

    use Telegram\Bot\Actions;
    use Telegram\Bot\Commands\Command;
    class startCommand extends Command{
        protected $name = "start";
        protected $description = "Start command to get started";
        public function handle(){
            $this->replyWithMessage(['text'=>'<b>bold</b>, <strong>bold</strong>
            <i>italic</i>, <em>italic</em>
            <a href="URL">inline URL</a>
            <code>inline fixed-width code</code>
            <pre>pre-formatted fixed-width code block</pre>',
            'parse_mode'=>'HTML']);
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