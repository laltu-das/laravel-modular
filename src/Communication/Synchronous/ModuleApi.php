<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Synchronous;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Laltu\Modular\LaravelModular;
use Laltu\Modular\Support\Module;
use LogicException;

final readonly class ModuleApi
{
    public function __construct(
        private Container $container,
        private LaravelModular $modular,
    ) {}

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
    public function resolve(string $interface): object
    {
        $resolved = $this->container->make($interface);

        if (! is_object($resolved)) {
            throw new LogicException("Public API [{$interface}] did not resolve to an object.");
        }

        return $resolved;
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
    public function resolveFromModule(string $interface, string $moduleName): object
    {
        $module = $this->modular->module($moduleName);

        // Verify the interface belongs to the module's public API
        $this->assertPublicApi($module, $interface);

        return $this->resolve($interface);
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
     * @return array<string, string> Map of interface => implementation
     *
     * @throws BindingResolutionException
     */
    public function getModuleApis(string $moduleName): array
    {
        $module = $this->modular->module($moduleName);
        $apis = [];

        foreach ($this->discoverModuleContracts($module) as $interface) {
            if (! $this->container->bound($interface)) {
                continue;
            }

            $apis[$interface] = $this->implementationFor($interface);
        }

        return $apis;
    }

    /**
     * Get all public APIs across all enabled modules.
     *
     * @return array<string, array<string, string>> Map of moduleName => [interface => implementation]
     * @throws BindingResolutionException
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
     *
     * @throws BindingResolutionException
     */
    public function getProviderModule(string $interface): ?string
    {
        foreach ($this->modular->modules() as $module) {
            if (in_array($interface, $this->discoverModuleContracts($module), true)) {
                return $module->name;
            }
        }

        return null;
    }

    private function implementationFor(string $interface): string
    {
        if (! $this->container instanceof IlluminateContainer) {
            return $interface;
        }

        $binding = $this->container->getBindings()[$interface] ?? null;
        $implementation = is_array($binding) ? ($binding['concrete'] ?? $interface) : $interface;

        return is_string($implementation) ? $implementation : get_debug_type($implementation);
    }

    /**
     * Discover all contract interfaces in a module's Contracts/ directory.
     *
     * @return list<class-string>
     *
     * @throws BindingResolutionException
     */
    private function discoverModuleContracts(Module $module): array
    {
        $contractsPath = $module->path('Contracts');
        $files = $this->container->make(Filesystem::class);

        if (! $files instanceof Filesystem || ! $files->isDirectory($contractsPath)) {
            return [];
        }

        $contracts = [];

        foreach ($files->files($contractsPath) as $file) {
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
     *
     * @throws BindingResolutionException
     */
    private function assertPublicApi(Module $module, string $interface): void
    {
        $contracts = $this->discoverModuleContracts($module);

        if (! in_array($interface, $contracts, true)) {
            throw new InvalidArgumentException(
                "Interface [{$interface}] is not part of module [{$module->name}]'s public API (Contracts/).",
            );
        }
    }
}
