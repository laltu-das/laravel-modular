<?php

declare(strict_types=1);

namespace Laltu\Modular\Queue;

final class ModuleQueueWorker
{
    private array $workers = [];

    public function register(string $module, string $queue, array $options = []): static
    {
        $this->workers[$module][$queue] = array_merge([
            'connection' => 'default',
            'tries' => 3,
        ], $options);
        return $this;
    }

    public function workers(): array
    {
        return $this->workers;
    }

    public function forModule(string $module): array
    {
        return $this->workers[$module] ?? [];
    }
}
