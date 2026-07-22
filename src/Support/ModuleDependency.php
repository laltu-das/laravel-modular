<?php

declare(strict_types=1);

namespace Laltu\Modular\Support;

final class ModuleDependency
{
    private array $dependencies = [];

    public function register(string $module, array $dependsOn): static
    {
        $this->dependencies[$module] = array_unique(array_merge(
            $this->dependencies[$module] ?? [],
            $dependsOn
        ));
        return $this;
    }

    public function graph(): array
    {
        return $this->dependencies;
    }

    public function resolveOrder(): array
    {
        $ordered = [];
        $visited = [];

        foreach (array_keys($this->dependencies) as $module) {
            $this->visit($module, $visited, $ordered);
        }

        return $ordered;
    }

    private function visit(string $module, array &$visited, array &$ordered): void
    {
        if (isset($visited[$module])) {
            return;
        }
        $visited[$module] = true;

        foreach ($this->dependencies[$module] ?? [] as $dependency) {
            $this->visit($dependency, $visited, $ordered);
        }

        $ordered[] = $module;
    }
}
