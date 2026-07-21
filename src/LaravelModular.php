<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular;

use Illuminate\Contracts\Events\Dispatcher;
use LaravelModular\LaravelModular\Contracts\TenantModuleVoter;
use LaravelModular\LaravelModular\Discovery\ModuleRepository;
use LaravelModular\LaravelModular\Exceptions\ModuleNotFound;
use LaravelModular\LaravelModular\Support\CurrentTenant;
use LaravelModular\LaravelModular\Support\Module;

final readonly class LaravelModular
{
    public function __construct(
        private ModuleRepository $modules,
        private CurrentTenant $tenant,
        private ?TenantModuleVoter $voter,
        private Dispatcher $events,
    ) {
        //
    }

    /**
     * Modules enabled for the current tenant. Modules without a `.disabled`
     * file that the configured tenant voter also allows.
     *
     * @return array<string, Module>
     */
    public function modules(): array
    {
        return array_filter($this->modules->all(), fn (Module $module): bool => $this->isEnabled($module));
    }

    public function module(string $name): Module
    {
        foreach ($this->modules() as $module) {
            if (strcasecmp($module->name, $name) === 0) {
                return $module;
            }
        }

        throw new ModuleNotFound("Module [{$name}] does not exist or is not enabled.");
    }

    public function has(string $name): bool
    {
        foreach ($this->modules() as $module) {
            if (strcasecmp($module->name, $name) === 0) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function moduleNames(): array
    {
        return array_values(array_map(fn (Module $module): string => $module->name, $this->modules()));
    }

    public function isEnabled(Module $module): bool
    {
        return $this->voter === null || $this->voter->allows($module, $this->tenant());
    }

    /**
     * The tenant resolved by the configured TenantResolver, or null when no
     * tenancy is in play.
     */
    public function tenant(): mixed
    {
        return $this->tenant->get();
    }

    /**
     * Publish an event to every module listening for it. This is the backbone
     * of low-coupling, event-driven communication between modules.
     *
     * @return array<int, mixed>|null
     */
    public function publish(object $event): ?array
    {
        $response = $this->events->dispatch($event);

        return is_array($response) ? array_values($response) : null;
    }

    /**
     * Subscribe a listener (class name or callable) to one or more events.
     * Wildcard patterns such as `Modules\Billing\Events\*` are supported.
     */
    public function listen(array|string $events, callable|string|null $listener = null): void
    {
        $this->events->listen($events, $listener);
    }
}
