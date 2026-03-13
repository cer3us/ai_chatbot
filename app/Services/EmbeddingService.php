<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected string $model;
    protected string $host;

    public function __construct()
    {
        $this->model = env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text');
        $this->host = env('OLLAMA_EMBEDDING_HOST', 'http://localhost:11434');
    }

    /**
     * Получить векторное представление текста.
     *
     * @param string $text
     * @return array
     * @throws \Exception
     */
    public function generate(string $text): array
    {
        $response = Http::post($this->host . '/api/embeddings', [
            'model' => $this->model,
            'prompt' => $text
        ]);

        if ($response->successful()) {
            return $response->json('embedding');
        }

        Log::error('Embedding failed', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        throw new \Exception('Failed to get embedding: ' . $response->body());
    }
}
