<?php

declare(strict_types=1);

namespace Laltu\Modular\Events;

use Laltu\Modular\Broadcasting\ModuleBroadcast;
use Laltu\Modular\Support\Module;

/**
 * Fired before a module is booted.
 */
final readonly class ModuleBooting
{
    private float $startTime;

    public function __construct(public Module $module, public mixed $tenant = null)
    {
        $this->startTime = microtime(true);
    }

    public function description(): string
    {
        return "Module [{$this->module->name}] is booting for tenant [" . (is_string($this->tenant) ? $this->tenant : get_debug_type($this->tenant)) . "].";
    }

    public function isEnabled(): bool
    {
        return $this->module->exists() && ! $this->module->isDisabled();
    }

    public function moduleName(): string
    {
        return $this->module->name;
    }

    public function bootDurationMs(): float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }

    public function broadcast(): void
    {
        $broadcast = new ModuleBroadcast($this->module->name);
        $broadcast->broadcast($this, 'modular.events');
    }

    public function auditLog(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'event' => 'ModuleBooting',
            'module' => $this->module->name,
            'path' => $this->module->path(),
            'tenant' => is_string($this->tenant) ? $this->tenant : null,
        ];
    }

    public function toArray(): array
    {
        return array_merge([
            'event' => 'ModuleBooting',
            'module' => $this->module->name,
            'namespace' => $this->module->namespace,
            'path' => $this->module->path(),
            'tenant' => $this->tenant,
            'enabled' => $this->isEnabled(),
        ], ['duration_ms' => $this->bootDurationMs()]);
    }
}
