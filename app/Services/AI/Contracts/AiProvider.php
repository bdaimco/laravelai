<?php

namespace App\Services\AI\Contracts;

interface AiProvider
{
    public function generate(string $prompt, array $options = []): AiResult;
}