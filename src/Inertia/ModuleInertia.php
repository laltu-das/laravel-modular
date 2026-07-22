<?php

declare(strict_types=1);

namespace Laltu\Modular\Inertia;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;

/**
 * Module-aware Inertia controller trait.
 */
trait ModuleInertia
{
    /**
     * Render an Inertia component with module props.
     */
    protected function renderInertia(string $component, array $props = []): mixed
    {
        $module = $this->resolveModuleFromController();

        return InertiaResponse::forModule($module)
            ->component($component)
            ->with($props)
            ->render();
    }

    private function resolveModuleFromController(): ?string
    {
        $class = get_class($this);
        $namespace = trim(App::bound('laravel-modular.namespace') ? config('laravel-modular.namespace', 'Modules') : 'Modules', '\\');

        if (str_starts_with($class, $namespace)) {
            $parts = explode('\\', substr($class, strlen($namespace) + 1));
            return $parts[0] ?? null;
        }

        return null;
    }
}
