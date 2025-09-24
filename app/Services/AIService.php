<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = env('AI_API_KEY');
        $this->apiUrl = env('AI_API_BASE', 'https://openrouter.ai.api/v1/chat/completions');
        $this->model = env('AI_MODEL', 'gpt-3.5-turbo');
    }
    public function chat(array $messages, int $maxTokens = 500): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ]) ->post($this->apiUrl, [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
        ]);
        
        if ($response->failed()) {
            throw new \Exception('API request failed: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

}