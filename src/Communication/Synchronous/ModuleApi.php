<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Synchronous;

use BackedEnum;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Laltu\Modular\Discovery\ModuleRepository;
use Laltu\Modular\LaravelModular;
use Laltu\Modular\Support\Module;

/**
 * Provides synchronous (method call) communication between modules.
 *
 * Modules expose their public API through interfaces in their Contracts/ directory.
 * Implementations are registered in the module's service provider.
 * Other modules can resolve these interfaces via the service container.
 *
 * Usage:
 * ```php
 * // In Module A's service provider or any class
 * use Modules\Billing\Contracts\InvoiceGateway;
 *
 * $gateway = ModuleApi::resolve(InvoiceGateway::class);
 * $gateway->charge(100);
 *
 * // Or with the LaravelModular facade
 * $gateway = Laltu\Modular::api(InvoiceGateway::class);
 * ```
 */
final readonly class ModuleApi
{
    public function __construct(
        private Container $container,
        private ModuleRepository $modules,
        private LaravelModular $modular,
    ) {}

    /**
     * Resolve a public API interface from any enabled module.
     *
     * @template T of object
     * @param class-string<T> $interface
     * @return T
     */
    public function resolve(string $interface): object
    {
        return $this->container->make($interface);
    }

    /**
     * Resolve a public API interface from a specific module.
     *
     * @template T of object
     * @param class-string<T> $interface
     * @param string $moduleName
     * @return T
     */
    public function resolveFromModule(string $interface, string $moduleName): object
    {
        $module = $this->modular->module($moduleName);

        // Verify the interface belongs to the module's public API
        $this->assertPublicApi($module, $interface);

        return $this->container->make($interface);
    }

    /**
     * Check if a module provides a specific public API interface.
     */
    public function has(string $interface): bool
    {
        return $this->container->bound($interface);
    }

    /**
     * Get all public API interfaces provided by a module.
     *
     * @return array<class-string, class-string> Map of interface => implementation
     */
    public function getModuleApis(string $moduleName): array
    {
        $module = $this->modular->module($moduleName);
        $apis = [];

        foreach ($this->discoverModuleContracts($module) as $interface) {
            if ($this->container->bound($interface)) {
                $apis[$interface] = $this->container->getBindings()[$interface]['concrete'] ?? $interface;
            }
        }

        return $apis;
    }

    /**
     * Get all public APIs across all enabled modules.
     *
     * @return array<string, array<class-string, class-string>> Map of moduleName => [interface => implementation]
     */
    public function getAllApis(): array
    {
        $allApis = [];

        foreach ($this->modular->modules() as $module) {
            $apis = $this->getModuleApis($module->name);
            if ($apis !== []) {
                $allApis[$module->name] = $apis;
            }
        }

        return $allApis;
    }

    /**
     * Get the module that provides a specific interface.
     */
    public function getProviderModule(string $interface): ?string
    {
        foreach ($this->modular->modules() as $module) {
            foreach ($this->discoverModuleContracts($module) as $contract) {
                if ($contract === $interface) {
                    return $module->name;
                }
            }
        }

        return null;
    }

    /**
     * Discover all contract interfaces in a module's Contracts/ directory.
     *
     * @return list<class-string>
     */
    private function discoverModuleContracts(Module $module): array
    {
        $contractsPath = $module->path('Contracts');

        if (! $this->container->make(\Illuminate\Filesystem\Filesystem::class)->isDirectory($contractsPath)) {
            return [];
        }

        $files = $this->container->make(\Illuminate\Filesystem\Filesystem::class)->files($contractsPath);
        $contracts = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $module->namespace.'\\Contracts\\'.$file->getFilenameWithoutExtension();

            if (interface_exists($className)) {
                $contracts[] = $className;
            }
        }

        return $contracts;
    }

    /**
     * Assert that an interface is part of a module's public API.
     */
    private function assertPublicApi(Module $module, string $interface): void
    {
        $contracts = $this->discoverModuleContracts($module);

        if (! in_array($interface, $contracts, true)) {
            throw new \InvalidArgumentException(
                "Interface [{$interface}] is not part of module [{$module->name}]'s public API (Contracts/)."
            );
        }
    }
}