<?php

declare(strict_types=1);

namespace Laltu\Modular\Inertia;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Module-aware Inertia response builder.
 *
 * Works with or without inertiajs/inertia-laravel installed.
 * When installed, delegates to Inertia::render(); otherwise
 * provides scaffolding and helpers for module-level props.
 */
final class InertiaResponse
{
    private array $props = [];
    private ?string $component = null;
    private array $sharedProps = [];

    public function __construct(
        private ?string $module = null,
        private ?Request $request = null,
    ) {}

    /**
     * Set the component name.
     */
    public function component(string $component): static
    {
        $this->component = $component;
        return $this;
    }

    /**
     * Add props to the response.
     */
    public function with(array|string $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->props = array_merge($this->props, $key);
        } else {
            $this->props[$key] = $value;
        }
        return $this;
    }

    /**
     * Set shared props for every module response.
     */
    public function share(string $key, mixed $value): static
    {
        $this->sharedProps[$key] = $value;
        return $this;
    }

    /**
     * Render the Inertia response using module-aware props.
     */
    public function render(): mixed
    {
        $component = $this->component ?? 'App';
        $props = array_merge($this->resolveModuleProps(), $this->sharedProps, $this->props);

        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render($component, $props);
        }

        return response()->json([
            'component' => $component,
            'props' => $props,
            'url' => $this->request?->url() ?? '/',
            'version' => config('app.version', '1'),
        ]);
    }

    /**
     * Resolve default props from the current module (if set).
     */
    private function resolveModuleProps(): array
    {
        $props = [];

        if ($this->module !== null && App::bound('laravel-modular.module')) {
            $props['module'] = $this->module;
            $props['module_name'] = $this->module;
            $props['module_namespace'] = 'Modules\\'.Str::studly($this->module);
        }

        return $props;
    }

    /**
     * Create a response for a specific module.
     */
    public static function forModule(?string $module): static
    {
        return new static($module, request());
    }

    /**
     * Get current module from request (if any).
     */
    public static function currentModule(): ?string
    {
        $route = request()?->route();
        $moduleParam = $route?->parameter('module') ?? null;

        return is_string($moduleParam) ? Str::studly($moduleParam) : null;
    }
}
