<?php

declare(strict_types=1);

namespace Laltu\Modular\Queue;

final class ModuleRetryPolicy
{
    private array $policies = [];

    public function register(string $queue, int $retries = 3, int $backoff = 60): static
    {
        $this->policies[$queue] = [
            'retries' => $retries,
            'backoff' => $backoff,
        ];
        return $this;
    }

    public function forQueue(string $queue): ?array
    {
        return $this->policies[$queue] ?? null;
    }

    public function all(): array
    {
        return $this->policies;
    }
}
