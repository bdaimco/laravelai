<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\Contracts\AiResult;
use Illuminate\Support\Facades\Http;

final class AnthropicProvider implements AiProvider
{
    public function __construct(private readonly array $config) {}

    public function generate(string $prompt, array $options = []): AiResult
    {
        $model = $options['model'] ?? $this->config['model'];
        $response = Http::withHeaders([
            'x-api-key' => $this->config['api_key'],
            'anthropic-version' => '2023-06-01',
        ])->post($this->config['base_url'].'/messages', [
            'model' => $model,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ])->throw();

        return new AiResult(
            $response->json('content.0.text', ''),
            'anthropic',
            $response->json('model', $model),
            $response->json(),
        );
    }
}