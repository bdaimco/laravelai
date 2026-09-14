<?php

namespace App\Services\AI;

use App\Models\CmsRegistry;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class AiConsoleService
{
    public function __construct(private readonly AiService $ai) {}

    public function providers(): array
    {
        return array_keys(config('ai.providers', []));
    }

    public function preview(string $prompt, ?string $provider = null, array $options = []): AiPreview
    {
        $result = $this->ai->generate($prompt, $provider, $options);

        return new AiPreview($prompt, $result, $this->normalizeFiles($result->text));
    }

    public function commit(
        AiPreview $preview,
        string $confirmedFingerprint,
        string $name,
        string $type = 'module',
        ?string $slug = null,
    ): CmsRegistry {
        if (! hash_equals($preview->fingerprint, $confirmedFingerprint)) {
            throw new InvalidArgumentException('The preview has changed. Generate and review a new preview before saving.');
        }

        return CmsRegistry::create([
            'name' => $name,
            'type' => $type,
            'slug' => $slug ?: Str::slug($name),
            'structure' => [
                'prompt' => $preview->prompt,
                'model' => $preview->result->model,
                'files' => $preview->files,
                'tree' => $preview->tree(),
            ],
            'ai_provider' => $preview->result->provider,
        ]);
    }

    private function normalizeFiles(string $text): array
    {
        $decoded = json_decode($text, true);

        if (is_array($decoded) && isset($decoded['files']) && is_array($decoded['files'])) {
            return array_map(fn (array $file): array => $this->validateFile($file), $decoded['files']);
        }

        return [['path' => 'AI_OUTPUT.md', 'content' => $text]];
    }

    private function validateFile(array $file): array
    {
        $path = (string) ($file['path'] ?? '');

        if ($path === '' || Str::startsWith($path, '/') || Str::contains($path, ['../', '..\\'])) {
            throw new InvalidArgumentException('AI output contains an unsafe file path.');
        }

        return ['path' => $path, 'content' => (string) ($file['content'] ?? '')];
    }
}