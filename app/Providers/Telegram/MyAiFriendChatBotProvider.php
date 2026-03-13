<?php

namespace App\Providers\Telegram;

use Illuminate\Support\ServiceProvider;
use Romanlazko\LaravelTelegram\Bot;

class MyAiFriendChatBotProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('MyAiFriendChatBot', function () {
            return Bot::make()
                ->id('8660646065')
                ->token('8660646065:AAF1AypfQ5jOE4EY6sJ8s9H2E5xUl22l67A')
                // works with errors, so calling a function from the 'Command' class:
                // ->discoverCommands(app_path('Telegram/MyAiFriendChatBot/Commands'), "App\Telegram\MyAiFriendChatBot\Commands")
                ->getCommandClassFromUpdateUsing(function ($update) {
                    // Если это текстовое сообщение, возвращаем наш класс
                    if (isset($update->message->text)) {
                        return \App\Telegram\MyAiFriendChatBot\Commands\DefaultCommand::class;
                    }
                    return \App\Telegram\MyAiFriendChatBot\Commands\StartCommand::class;
                })
                ->inlineDataStructure([
                    'temp' => null,
                ]);
        });
    }
}
