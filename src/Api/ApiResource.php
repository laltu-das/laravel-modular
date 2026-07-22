<?php

declare(strict_types=1);

namespace Laltu\Modular\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base API resource with module awareness.
 */
abstract class ApiResource extends JsonResource
{
    /**
     * Get the module name from the resource's underlying model or request.
     */
    public function module(): ?string
    {
        $modelClass = $this->resource ? get_class($this->resource) : null;
        $namespace = trim(config('laravel-modular.namespace', 'Modules'), '\\');

        if ($modelClass !== null && str_starts_with($modelClass, $namespace)) {
            $parts = explode('\\', substr($modelClass, strlen($namespace) + 1));
            return $parts[0] ?? null;
        }

        return null;
    }

    /**
     * Include module metadata in the resource response.
     */
    public function withModuleMeta(): array
    {
        return array_filter([
            'module' => $this->module(),
            'namespace' => $this->module() ? 'Modules\\'.str_replace('\\', '', $this->module()) : null,
        ]);
    }

    abstract public function toArray($request): array;
}
