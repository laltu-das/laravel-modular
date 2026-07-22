<?php

declare(strict_types=1);

namespace Laltu\Modular\Support;

final class ModuleHealth
{
    private array $checks = [];

    public function addCheck(string $name, \Closure $check): static
    {
        $this->checks[$name] = $check;
        return $this;
    }

    public function run(string $moduleName): array
    {
        $results = [];
        $basePath = rtrim(config('laravel-modular.path', base_path('Modules')), '/') . '/' . $moduleName;

        $results['exists'] = is_dir($basePath);
        $results['disabled_marker'] = is_file($basePath . '/.disabled');
        $results['routes_web'] = is_file($basePath . '/routes/web.php');
        $results['routes_api'] = is_file($basePath . '/routes/api.php');
        $results['migrations'] = is_dir($basePath . '/database/migrations');

        foreach ($this->checks as $name => $check) {
            $results[$name] = ($check)();
        }

        return $results;
    }
}
