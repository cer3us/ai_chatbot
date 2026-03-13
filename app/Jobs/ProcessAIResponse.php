<?php

namespace App\Jobs;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class ProcessAIResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $userMessage,
        private Conversation $conversation
    ) {}

    public function handle(AIService $aiService): void
    {
        try {

            $aiService->generateResponse($this->userMessage, $this->conversation);
            
        } catch (\Exception $e) {
            logger()->error('AI response failed: ' . $e->getMessage());

            $errorMessage = Message::create([
                'conversation_id' => $this->conversation->id,
                'user_id' => null,
                'content' => 'I apologize, but I\'m experiencing technical difficulties. Please try again in a moment.',
                'is_ai' => true,
            ]);

            broadcast(new MessageSent($errorMessage));
        }
    }
}