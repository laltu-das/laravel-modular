<?php

declare(strict_types=1);

namespace Laltu\Modular;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Laltu\Modular\Communication\Asynchronous\Message;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Laltu\Modular\Communication\Synchronous\ModuleApi;
use Laltu\Modular\Contracts\TenantModuleVoter;
use Laltu\Modular\Discovery\ModuleRepository;
use Laltu\Modular\Exceptions\ModuleNotFound;
use Laltu\Modular\Support\CurrentTenant;
use Laltu\Modular\Support\Module;
use LogicException;

final readonly class LaravelModular
{
    public function __construct(
        private Container $container,
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
     * Subscribe a listener (class name or closure) to one or more events.
     * Wildcard patterns such as `Modules\Billing\Events\*` are supported.
     *
     * @param  string|array<int, string>  $events
     */
    public function listen(string|array $events, Closure|string|null $listener = null): void
    {
        $this->events->listen($events, $listener);
    }

    /**
     * Resolve a public API interface from any enabled module.
     *
     * @template T of object
     *
     * @param  class-string<T>  $interface
     * @return T
     *
     * @throws BindingResolutionException
     */
    public function api(string $interface): object
    {
        return $this->moduleApi()->resolve($interface);
    }

    /**
     * Resolve a public API interface from a specific module.
     *
     * @template T of object
     *
     * @param  class-string<T>  $interface
     * @return T
     *
     * @throws BindingResolutionException
     */
    public function apiFrom(string $interface, string $moduleName): object
    {
        return $this->moduleApi()->resolveFromModule($interface, $moduleName);
    }

    /**
     * Check if any enabled module has bound the given public API interface.
     */
    public function hasApi(string $interface): bool
    {
        return $this->moduleApi()->has($interface);
    }

    /**
     * Get all public APIs across all enabled modules.
     *
     * @return array<string, array<string, string>>
     *
     * @throws BindingResolutionException
     */
    public function allApis(): array
    {
        return $this->moduleApi()->getAllApis();
    }

    /**
     * Find which enabled module declares the given public API interface.
     * @throws BindingResolutionException
     */
    public function getProviderModule(string $interface): ?string
    {
        return $this->moduleApi()->getProviderModule($interface);
    }

    /**
     * Get the asynchronous message bus.
     */
    public function messageBus(): MessageBus
    {
        $messageBus = $this->container->make(MessageBus::class);

        if (! $messageBus instanceof MessageBus) {
            throw new LogicException('The message bus service is not registered correctly.');
        }

        return $messageBus;
    }

    /**
     * Publish a message to its configured channel.
     */
    public function publishMessage(Message $message): string
    {
        return $this->messageBus()->publish($message);
    }

    /**
     * Publish a message to its configured channel after a delay.
     * @throws BindingResolutionException
     */
    public function publishMessageLater(Message $message, int $delay): string
    {
        return $this->messageBus()->publishLater($message, $delay);
    }

    private function moduleApi(): ModuleApi
    {
        $moduleApi = $this->container->make(ModuleApi::class);

        if (! $moduleApi instanceof ModuleApi) {
            throw new LogicException('The module API service is not registered correctly.');
        }

        return $moduleApi;
    }
}
