<?php

namespace App\Services\AI\Contracts;

final readonly class AiResult
{
    public function __construct(
        public string $text,
        public string $provider,
        public string $model,
        public array $raw = [],
    ) {}
}