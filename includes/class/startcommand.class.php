<?php
    namespace Vendor\App\Commands;

    use Telegram\Bot\Actions;
    use Telegram\Bot\Commands\Command;
    use Telegram\Bot\Keyboard\Keyboard;
    use Telegram;
    class startCommand extends Command{
        protected $name = "start";
        protected $description = "Start command to get started";
        public function handle(){
            $this->replyWithMessage(['text'=>'
            <b>Welcome to Cowin vaccine tracker!</b>
            <code>We will help you notifying when vaccine is available within your district or pincode.Please choose appropriate options from the menu given below.</code>
            <i>Please don\'t spam with random commands!</i>',
            'parse_mode'=>'HTML']);
            $this->replyWithChatAction(['action'=>Actions::TYPING]);



            $inlineLayout = [
                [
                    Keyboard::inlineButton(['text' => 'Test', 'callback_data' => 'data']),
                    Keyboard::inlineButton(['text' => 'Btn 2', 'callback_data' => 'data_from_btn2'])
                ]
            ];
    
            $keyboard = Telegram::replyKeyboardMarkup([
                'inline_keyboard' => $inlineLayout
            ]);
    
            $this->replyWithMessage(['text' => 'Start command', 'reply_markup' => $keyboard]);



            $this->triggerCommand('subscribe');
        }
    }
?>