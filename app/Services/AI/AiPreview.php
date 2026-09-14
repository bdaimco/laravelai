<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AiResult;

final readonly class AiPreview
{
    public string $fingerprint;

    public function __construct(
        public string $prompt,
        public AiResult $result,
        public array $files,
    ) {
        $this->fingerprint = hash('sha256', json_encode([
            'prompt' => $prompt,
            'provider' => $result->provider,
            'model' => $result->model,
            'files' => $files,
        ], JSON_THROW_ON_ERROR));
    }

    public function tree(): array
    {
        $tree = [];

        foreach ($this->files as $file) {
            $parts = explode('/', $file['path']);
            $branch =& $tree;

            foreach ($parts as $part) {
                $branch[$part] ??= [];
                $branch =& $branch[$part];
            }

            $branch = ['__file' => true, 'content' => $file['content']];
            unset($branch);
        }

        return $tree;
    }
}