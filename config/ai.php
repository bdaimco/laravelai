<?php

return [
    'default' => env('AI_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'class' => App\Services\AI\Providers\OpenAiProvider::class,
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
        ],
        'huggingface' => [
            'class' => App\Services\AI\Providers\HuggingFaceProvider::class,
            'base_url' => env('HUGGINGFACE_BASE_URL', 'https://api-inference.huggingface.co/models'),
            'api_key' => env('HUGGINGFACE_API_KEY'),
            'model' => env('HUGGINGFACE_MODEL', 'mistralai/Mistral-7B-Instruct-v0.2'),
        ],
        'anthropic' => [
            'class' => App\Services\AI\Providers\AnthropicProvider::class,
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
        ],
    ],
];