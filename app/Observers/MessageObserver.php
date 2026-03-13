<?php

namespace App\Observers;

use App\Models\Message;
use App\Jobs\ExtractFactsFromMessage;
use Illuminate\Support\Facades\Log;

class MessageObserver
{

    public function created(Message $message): void
    {
        Log::info('MessageObserver created fired', ['message_id' => $message->id, 'content' => $message->content]);
        if ($message->is_ai) {
            return;
        }

        // Диспатчим задание — оно уйдёт в очередь и не замедлит ответ
        ExtractFactsFromMessage::dispatch($message);
    }
}