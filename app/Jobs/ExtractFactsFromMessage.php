<?php

namespace App\Jobs;

use App\Services\EmbeddingService;
use App\Models\Message;
use App\Models\MemoryFact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Illuminate\Support\Facades\Log;

class ExtractFactsFromMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [5, 15, 30];

    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        Log::info('ExtractFacts started', ['text' => $this->message->content]);

        if ($this->message->is_ai) {
            return;
        }

        $embeddingService = app(EmbeddingService::class);

        $text = $this->message->content;
        $conversation = $this->message->conversation;
        $sessionId = 'user_' . ($conversation->user_id ?? 'guest');

        $explicitFacts = $this->extractExplicitFacts($text);
        foreach ($explicitFacts as $fact) {
            $this->storeFact($fact, $sessionId, $conversation, 'explicit_command', $embeddingService);
        }

        if (empty($explicitFacts) || !preg_match('/^(fact|remember|memorize):/i', $text)) {
            $llmFacts = $this->extractFactsWithLLM($text);
            $filteredFacts = $this->filterFacts($llmFacts);
            foreach ($filteredFacts as $fact) {
                if (!$this->factExists($fact, $sessionId, $embeddingService)) {
                    $this->storeFact($fact, $sessionId, $conversation, 'llm_extraction', $embeddingService);
                }
            }
        }
    }

    private function extractExplicitFacts(string $text): array
    {
        $facts = [];
        if (preg_match('/^(fact|remember|memorize):\s*(.+)$/i', $text, $matches)) {
            $facts[] = trim($matches[2]);
        }
        return $facts;
    }

    private function extractFactsWithLLM(string $text): array
    {
        $prompt = <<<PROMPT
You are an extractor of personal facts about a user. 
Extract concrete, factual attributes about the user from the following message.
Only extract what user specifically mentioned himself, do not predict, imagine or make assumptions about the possible facts yourself.
Valid facts include:
- name (e.g., "User's name is Alex")
- age (e.g., "User is 30 years old")
- birthday (e.g., "User's birthday is Jan 1")
- likes/dislikes (e.g., "User likes horror books", "User dislikes coffee")
- preferences (e.g., "User prefers milk over coffee")
- hobbies (e.g., "User enjoys developing")
- learning topics (e.g., "User is learning Laravel 12")
- profession (e.g., "User is a developer")

Do NOT extract meta statements about the conversation, such as:
- "User asks for recommendations"
- "User wants to know"
- "User is asking"
- "No information provided"
- "... is not specified"
- any phrase containing "seeks", "wants", "asks", "would like to know", "could you repeat"

Return ONLY a JSON array of strings. Each string should be a complete fact about the user.
Do not include any other text, explanation, or markdown. The response must be valid JSON.

If no facts are found, return an empty array.

User Message: "$text"

Facts (JSON array):
PROMPT;

        try {
            //// For Ollama local models:
            // $response = Prism::text()
            //     ->using(Provider::Ollama, env('OLLAMA_EXTRACTION_MODEL', 'phi3:mini'))
            //     ->withPrompt($prompt)
            //     ->withClientOptions(['timeout' => 200])
            //     ->usingTemperature(0.1)
            //     ->asText();

            //// For Ollama cloud models:
            $response = Prism::text()
                ->using(Provider::Ollama, env('OLLAMA_EXTRACTION_MODEL', 'phi3:mini'))
                ->withClientOptions([
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('OLLAMA_API_KEY'),
                    ],
                    'timeout' => 300,
                ])
                ->withPrompt($prompt)
                ->usingTemperature(0.1)
                ->asText();

            $content = trim($response->text);
            Log::info('❕LLM extracted facts in raw', ['response' => $content]);

            if (str_starts_with($content, '```')) {
                $content = preg_replace('/^```(json)?\n|\n```$/u', '', $content);
            }

            $facts = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($facts)) {
                Log::warning('LLM fact extraction returned invalid JSON', ['response' => $content]);
                return [];
            }

            return $facts;
        } catch (\Exception $e) {
            Log::error('LLM fact extraction failed: ' . $e->getMessage());
            return [];
        }
    }

    private function filterFacts(array $facts): array
    {
        $originalCount = count($facts);

        $forbiddenPatterns = [
            '/\b(ask|asks|asked|asking)\b/i',
            '/\b(want|wants|wanted|wanting)\b/i',
            '/\b(seek|seeks|seeking)\b/i',
            '/\b(recommend|recommends|recommendation|recommendations)\b/i',
            '/\b(repeat|repetition)\b/i',
            '/\b(could you|would you)\b/i',
            '/\b(not provided|no information|unknown)\b/i',
            '/\b(preferences?)\b/i',
        ];

        $filtered = array_filter($facts, function ($fact) use ($forbiddenPatterns) {
            if (!is_string($fact) || trim($fact) === '') {
                return false;
            }

            $lower = strtolower($fact);
            foreach ($forbiddenPatterns as $pattern) {
                if (preg_match($pattern, $lower)) {
                    return false;
                }
            }

            if (strlen(trim($fact)) < 5) {
                return false;
            }

            return true;
        });

        $filteredCount = count($filtered);
        if ($originalCount > 0 && $filteredCount === 0) {
            Log::info('All facts filtered out', ['original_facts' => $facts]);
        }

        return array_values($filtered);
    }

    private function storeFact(string $fact, string $sessionId, $conversation, string $source, EmbeddingService $embeddingService): void
    {
        try {
            $fact = trim($fact);
            $fact = ucfirst($fact);
            if (!str_ends_with($fact, '.')) {
                $fact .= '.';
            }

            $embedding = $embeddingService->generate($fact);

            MemoryFact::create([
                'session_id' => $sessionId,
                'content' => $fact,
                'embedding' => $embedding,
                'metadata' => [
                    'conversation_id' => $conversation->id,
                    'message_id' => $this->message->id,
                    'source' => $source
                ],
            ]);
            Log::info('🕵️Fact stored', ['fact' => $fact, 'source' => $source]);
        } catch (\Exception $e) {
            Log::error('Failed to store fact: ' . $e->getMessage());
        }
    }

    private function factExists(string $fact, string $sessionId, EmbeddingService $embeddingService): bool
    {
        $embedding = $embeddingService->generate($fact);

        $similar = MemoryFact::where('session_id', $sessionId)
            ->whereRaw('embedding <=> ? < 0.3', [json_encode($embedding)])
            ->orderByRaw('embedding <=> ?', [json_encode($embedding)])
            ->first();

        if ($similar) {
            Log::info('Similar fact already exists', ['existing' => $similar->content]);
            return true;
        }

        return MemoryFact::where('session_id', $sessionId)
            ->whereRaw('LOWER(content) = ?', [strtolower($fact)])
            ->exists();
    }
}
