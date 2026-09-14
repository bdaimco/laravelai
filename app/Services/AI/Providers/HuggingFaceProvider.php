<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\Contracts\AiResult;
use Illuminate\Support\Facades\Http;

final class HuggingFaceProvider implements AiProvider
{
    public function __construct(private readonly array $config) {}

    public function generate(string $prompt, array $options = []): AiResult
    {
        $model = $options['model'] ?? $this->config['model'];
        $response = Http::withToken($this->config['api_key'])
            ->post($this->config['base_url'].'/'.$model, ['inputs' => $prompt])
            ->throw();
        $text = $response->json('0.generated_text', $response->json('generated_text', ''));

        return new AiResult($text, 'huggingface', $model, $response->json());
    }
}