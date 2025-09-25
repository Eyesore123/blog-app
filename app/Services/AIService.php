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

    public function checkStyle(string $draft): string
    {
        $messages = [
            ['role' => 'system', 'content' => 'You are a writing style assistant.'],
            ['role' => 'user', 'content' => "Review this draft and give tone/style feedback with 3 improvement tips:\n\n$draft"],
        ];

        return $this->chat($messages);
    }

    public function generateIdeas(array $posts): string
    {
        $context = collect($posts)
            ->map(fn ($post) => "- {$post['title']} : {$post['content']}")
            ->join("\n");
    
        $messages = [
            ['role' => 'system', 'content' => 'You are a blog idea generator.'],
            ['role' => 'user', 'content' => "Here are some recent posts: \n$context\n\nSuggest 5 fresh post ideas in a similar style."],
        ];
        
        return $this->chat($messages);
    }
}