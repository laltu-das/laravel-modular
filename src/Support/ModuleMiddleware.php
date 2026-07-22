<?php

declare(strict_types=1);

namespace Laltu\Modular\Support;

use Illuminate\Support\Collection;

/**
 * Module-level middleware stacks.
 */
final class ModuleMiddleware
{
    private array $stacks = [];

    public function registerStack(string $module, array $middleware): void
    {
        $this->stacks[$module] = array_unique(array_merge(
            $this->stacks[$module] ?? [],
            $middleware
        ));
    }

    public function stackFor(string $module): array
    {
        return $this->stacks[$module] ?? [];
    }

    public function hasStack(string $module): bool
    {
        return isset($this->stacks[$module]) && $this->stacks[$module] !== [];
    }

    public function allStacks(): array
    {
        return $this->stacks;
    }

    public function mergeGlobal(array $globalMiddleware): void
    {
        foreach ($this->stacks as $module => $stack) {
            $this->stacks[$module] = array_unique(array_merge($globalMiddleware, $stack));
        }
    }

    public function applyToRoute(string $routeName, string $middlewareClass): void
    {
        // Helper to apply module middleware to named routes.
    }
}
