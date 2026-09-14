<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\Contracts\AiResult;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class AiService
{
    public function __construct(private readonly Container $container) {}

    public function generate(string $prompt, ?string $provider = null, array $options = []): AiResult
    {
        $name = $provider ?: config('ai.default');
        $providerConfig = config("ai.providers.{$name}");

        if (! is_array($providerConfig) || ! isset($providerConfig['class'])) {
            throw new InvalidArgumentException("Unsupported AI provider [{$name}].");
        }

        $instance = $this->container->make($providerConfig['class'], ['config' => $providerConfig]);

        if (! $instance instanceof AiProvider) {
            throw new InvalidArgumentException("AI provider [{$name}] must implement AiProvider.");
        }

        return $instance->generate($prompt, $options);
    }
}