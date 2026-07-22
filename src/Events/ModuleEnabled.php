<?php

declare(strict_types=1);

namespace Laltu\Modular\Events;

use Laltu\Modular\Broadcasting\ModuleBroadcast;

final readonly class ModuleEnabled
{
    public function __construct(public string $name)
    {
        //
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function exists(): bool
    {
        $basePath = rtrim(config('laravel-modular.path', base_path('Modules')), '/') . '/' . $this->name;
        return is_dir($basePath);
    }

    public function modulePath(): ?string
    {
        $path = rtrim(config('laravel-modular.path', base_path('Modules')), '/') . '/' . $this->name;
        return is_dir($path) ? $path : null;
    }

    public function broadcast(): void
    {
        $broadcast = new ModuleBroadcast($this->name);
        $broadcast->broadcast($this, 'modular.events');
    }

    public function auditLog(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'event' => 'ModuleEnabled',
            'name' => $this->name,
            'exists' => $this->exists(),
        ];
    }

    public function toArray(): array
    {
        return [
            'event' => 'ModuleEnabled',
            'name' => $this->name,
            'exists' => $this->exists(),
            'path' => $this->modulePath(),
        ];
    }
}
