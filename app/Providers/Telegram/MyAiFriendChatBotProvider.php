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
                ->token('8660646065:AAFDtwLFjKr66ayz86E78Fz8i_dR2wI5H6I')
                // ->discoverCommands(app_path('Telegram/MyAiFriendChatBot/Commands'), "App\\Telegram\\MyAiFriendChatBot\\Commands")
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
