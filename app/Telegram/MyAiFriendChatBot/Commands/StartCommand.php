<?php

namespace App\Telegram\MyAiFriendChatBot\Commands;

use Romanlazko\LaravelTelegram\Command;
use Romanlazko\LaravelTelegram\Models\Types\Chat;
use App\Services\AIService;
use App\Models\Conversation;
use App\Models\User;

class StartCommand extends Command
{
    protected static ?string $command = '/start';

    public function execute(Chat $chat)
    {
        $aiService = app(AIService::class);
        $telegramUserId = $chat->id;

        $user = User::firstOrCreate(
            ['telegram_id' => $telegramUserId],
            ['name' => $chat->first_name ?? 'Telegram User']
        );

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id],
            ['title' => 'Telegram Chat']
        );

        $this->apiMethod('sendMessage')
            ->chatId($chat->id)
            ->text("Hello! I'm your AI assistant. You can ask me anything.")
            ->parseMode('Markdown')
            ->send();
    }

    public function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'update' => [$this->getObject()],
            'message' => [$this->evaluate(fn($update) => $update->message ?? $update->callback_query->message)],
            'chat' => [$this->evaluate(fn($message) => $message->chat)],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}
