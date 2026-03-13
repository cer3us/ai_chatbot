<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\MemoryFact;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\Enums\Provider;
use App\Services\EmbeddingService;


class AIService
{
    protected string $model;
    protected string $provider;
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
        $this->provider = 'ollama';
        $this->model = env('OLLAMA_MODEL', 'qwen2.5-coder:3b');
    }

    //// Version 1:
    // public function generateResponse(string $message, Conversation $conversation): string
    // {

    //     //DEbug 
    //     Log::info('AIService called with memory', ['message' => $message]);

    //     $context = $this->buildContext($conversation);

    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $this->apiKey,
    //         'Content-Type' => 'application/json',
    //         'HTTP-Referer' => config('app.url'), 
    //     ])->post($this->apiUrl, [
    //         'model' => $this->model, 
    //         'messages' => array_merge($context, [
    //             ['role' => 'user', 'content' => $message]
    //         ]),
    //         'max_tokens' => 500,
    //         'temperature' => 0.7,
    //     ]);

    //     //Debug
    //     Log::info('OpenRouter response', ['response' => $response]);


    //     if ($response->successful()) {
    //         return $response->json('choices.0.message.content');
    //     }

    //     return 'I apologize, but I encountered an error processing your request. Please try again.';
    // }

    //Version 2 (with Prism and Ollama):
    public function generateResponse(string $message, Conversation $conversation): string
    {
        Log::info('AIService with Prism called', ['message' => $message]);

        $userId = $conversation->user_id ?? 'guest';
        $sessionId = 'user_' . $userId;

        $relevantMemories = $this->getRelevantMemories($message, $sessionId);
        $factsHash = $this->hashFacts($relevantMemories);
        $cacheKey = 'ai_response:' . md5($message . $sessionId . $factsHash);

        $cachedResponse = Cache::store('redis')->get($cacheKey);
        if ($cachedResponse) {
            Log::info('✅ AI response served from cache', ['message' => $message]);

            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'content' => $cachedResponse,
                'is_ai' => true,
            ]);
            broadcast(new MessageSent($aiMessage))->toOthers();

            return $cachedResponse;
        }

        try {
            $startTime = microtime(true);

            $messages = $this->buildContext($conversation, $message, $sessionId);
            $messages[] = new UserMessage($message);

            //// For Ollama local models:
            // $response = Prism::text()
            //     ->using(Provider::Ollama, $this->model)
            //     ->withMessages($messages)
            //     ->withClientOptions(['timeout' => 300])
            //     ->withProviderOptions([ 
            //         'top_p' => 0.9, 
            //         'num_ctx' => 4096, 
            //     ]) 
            //     ->asText();

            //// For Ollama Cloud:
            $response = Prism::text()
                ->using(Provider::Ollama, $this->model)
                ->withClientOptions([
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('OLLAMA_API_KEY'),
                    ],
                    'timeout' => 120,
                ])
                ->withMessages($messages)
                ->asText();

            Log::info('Prism response received', ['response' => $response->text]);

            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'content' => $response->text,
                'is_ai' => true,
                'metadata' => [
                    'model' => $this->model,
                    'processing_time' => microtime(true) - $startTime,
                ],
            ]);
            broadcast(new MessageSent($aiMessage))->toOthers();

            Cache::store('redis')->put($cacheKey, $response->text, now()->addDay());
            Log::info('🔵 Cached AI response', ['key' => $cacheKey]);

            return $response->text;
        } catch (\Exception $e) {
            Log::error('AI Service Error with Prism: ' . $e->getMessage());
            return 'I apologize, but I encountered an error processing your request. Please try again.';
        }
    }

    private function hashFacts(array $facts): string
    {
        if (empty($facts)) {
            return 'no_facts';
        }

        $ids = array_column($facts, 'id');
        if (!empty($ids) && $ids[0] !== null) {
            return md5(implode(',', $ids));
        }

        $contents = array_column($facts, 'content');
        return md5(implode('|', $contents));
    }

    private function getRelevantMemories(string $query, string $sessionId): array
    {
        $embedding = $this->getEmbedding($query);

        return MemoryFact::where('session_id', $sessionId)
            ->whereRaw('embedding <=> ? < 0.4', [json_encode($embedding)])
            ->orderByRaw('embedding <=> ?', [json_encode($embedding)])
            ->limit(3)
            ->get()
            ->toArray();
    }

    private function getEmbedding(string $text): array
    {
        return $this->embeddingService->generate($text);
    }

    private function buildContext(Conversation $conversation, string $currentMessage, string $sessionId): array
    {
        $systemPrompt = <<<PROMPT
        You are a helpful AI assistant. Provide concise, accurate, and friendly responses.

        IMPORTANT – CURRENT DATE: Today is {date}. You must use this date when calculating ages, answering "what is today's date", or any time‑related questions. Do not say you don't know the current date – it is provided above.

        Below are facts about the CURRENT USER (the person you are talking to):
        - Use these facts when asked about the user’s preferences, name, age, etc.
        - ALWAYS use these facts if they are relevant.
        - Never say "I like ..." when referring to the user’s likes – say "You like ..." or "The user likes ...".
        - Do not refer to the facts as "the facts".
        - If you don’t know the answer, say you don’t have that information.
        PROMPT;

        $systemPrompt = str_replace('{date}', now()->format('F j, Y'), $systemPrompt);
        $messages = [new SystemMessage($systemPrompt)];

        $relevantMemories = $this->getRelevantMemories($currentMessage, $sessionId);

        if (!empty($relevantMemories)) {
            $factsForLog = array_map(function ($fact) {
                return [
                    'id'         => $fact['id'],
                    'session_id' => $fact['session_id'],
                    'content'    => $fact['content'],
                ];
            }, $relevantMemories);

            Log::info('✅ Retrieved memories', [
                'count' => count($relevantMemories),
                'facts' => $factsForLog,
            ]);

            $factLines = collect($relevantMemories)->pluck('content')->map(fn($f) => "- $f")->implode("\n");
            $memoryText = "These facts from previous conversations are about the current user, not about you:\n" . $factLines;
            array_unshift($messages, new SystemMessage($memoryText));
        }

        $recentMessages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse();

        foreach ($recentMessages as $msg) {
            $messages[] = $msg->is_ai
                ? new AssistantMessage($msg->content)
                : new UserMessage($msg->content);
        }

        return $messages;
    }
}
