<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\Contracts\AiResult;
use Illuminate\Support\Facades\Http;

final class OpenAiProvider implements AiProvider
{
    public function __construct(private readonly array $config) {}

    public function generate(string $prompt, array $options = []): AiResult
    {
        $response = Http::withToken($this->config['api_key'])
            ->post($this->config['base_url'].'/chat/completions', [
                'model' => $options['model'] ?? $this->config['model'],
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ])
            ->throw();

        return new AiResult(
            $response->json('choices.0.message.content', ''),
            'openai',
            $response->json('model', $options['model'] ?? $this->config['model']),
            $response->json(),
        );
    }
}