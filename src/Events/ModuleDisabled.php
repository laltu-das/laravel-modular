<?php

declare(strict_types=1);

namespace Laltu\Modular\Events;

use Laltu\Modular\Broadcasting\ModuleBroadcast;

final readonly class ModuleDisabled
{
    public function __construct(public string $name)
    {
        //
    }

    public function isDisabled(): bool
    {
        return true;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function disabledMarkerExists(): bool
    {
        $basePath = rtrim(config('laravel-modular.path', base_path('Modules')), '/') . '/' . $this->name;
        return is_file($basePath . '/.disabled');
    }

    public function exists(): bool
    {
        $basePath = rtrim(config('laravel-modular.path', base_path('Modules')), '/') . '/' . $this->name;
        return is_dir($basePath);
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
            'event' => 'ModuleDisabled',
            'name' => $this->name,
            'exists' => $this->exists(),
            'disabled_marker_exists' => $this->disabledMarkerExists(),
        ];
    }

    public function toArray(): array
    {
        return [
            'event' => 'ModuleDisabled',
            'name' => $this->name,
            'exists' => $this->exists(),
            'disabled_marker_exists' => $this->disabledMarkerExists(),
        ];
    }
}
