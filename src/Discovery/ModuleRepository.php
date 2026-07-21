<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Discovery;

use Illuminate\Filesystem\Filesystem;
use LaravelModular\LaravelModular\Support\Module;

final class ModuleRepository
{
    /** @var array<string, Module>|null */
    private ?array $modules = null;

    public function __construct(private readonly Filesystem $files, private readonly string $path, private readonly string $namespace)
    {
        //
    }

    /**
     * Enabled modules below the module path. Pass $includeDisabled to also
     * list modules marked with a `.disabled` file (unmemoized).
     *
     * @return array<string, Module>
     */
    public function all(bool $includeDisabled = false): array
    {
        if (! $includeDisabled && $this->modules !== null) {
            return $this->modules;
        }

        if (! $this->files->isDirectory($this->path)) {
            $this->modules = [];

            return $this->modules;
        }

        $modules = [];

        foreach ($this->files->directories($this->path) as $directory) {
            $name = basename($directory);

            if (str_starts_with($name, '.')) {
                continue;
            }

            $disabled = is_file($directory.'/.disabled');

            if ($disabled && ! $includeDisabled) {
                continue;
            }

            $modules[$name] = new Module($name, $directory, trim($this->namespace, '\\').'\\'.$name, $disabled);
        }

        ksort($modules);

        if (! $includeDisabled) {
            $this->modules = $modules;
        }

        return $modules;
    }

    public function find(string $name): ?Module
    {
        foreach ($this->all() as $module) {
            if (strcasecmp($module->name, $name) === 0) {
                return $module;
            }
        }

        return null;
    }

    public function flush(): void
    {
        $this->modules = null;
    }
}
