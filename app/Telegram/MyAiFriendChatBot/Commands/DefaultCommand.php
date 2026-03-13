<?php

namespace App\Telegram\MyAiFriendChatBot\Commands;

use Romanlazko\LaravelTelegram\Command;
use Romanlazko\LaravelTelegram\Models\Types\Chat;
use App\Services\AIService;
use App\Models\Conversation;
use App\Models\User;

class DefaultCommand extends Command
{
    // Типизация ?string обязательна для PHP 8+ в этом пакете
    protected static ?string $command = 'default';

    public function execute(Chat $chat)
    {
        $aiService = app(AIService::class);

        // Получаем текст напрямую из объекта Update (или CallbackQuery)
        // Это обходит магический метод getMessage(), который вызывал ошибку
        $update = $this->getObject();
        $messageText = $update->message->text ?? $update->callback_query->message->text ?? '';

        if (empty($messageText)) return;


        $user = User::where('telegram_id', $chat->id)->first();

        if (!$user) {
            $user = User::create([
                'name'        => $chat->first_name ?? 'Telegram User',
                'telegram_id' => $chat->id,
                'email'       => $chat->id . '@t.me', // Фейковый email для валидации БД
                'password'    => bcrypt(str()->random(16)), // Случайный пароль
            ]);
        }


        $conversation = Conversation::firstOrCreate(
            ['user_id' => $user->id],
            ['title' => 'Telegram Chat']
        );

        // Сохраняем сообщение пользователя
        $conversation->messages()->create([
            'user_id' => $user->id,
            'content' => $messageText,
            'is_ai' => false,
        ]);

        // Получаем ответ от AI
        $aiResponse = $aiService->generateResponse($messageText, $conversation);

        $token = env('TELEGRAM_BOT_TOKEN');


        // Отправляем через HTTP-клиент, чтобы точно избежать багов пакета
        \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot$token/sendMessage", [
            'chat_id'    => $chat->id,
            'text'       => $aiResponse,
            // Уберите Markdown пока что, так как Ollama выдает много спецсимволов, 
            // которые ломают отправку при неправильном экранировании.
        ]);
    }



    // Метод resolve... лучше оставить как в примере, если вы используете DI в аргументах execute
    public function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'update'  => [$this->getObject()],
            'message' => [$this->evaluate(fn($update) => $update->message ?? $update->callback_query->message)],
            'chat'    => [$this->evaluate(fn($message) => $message->chat)],
            default   => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}
