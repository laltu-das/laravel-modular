<?php

declare(strict_types=1);

namespace Laltu\Modular\Testing;

final class ModuleFactories
{
    public static function forModule(string $module, string $model, int $count = 1): \Illuminate\Database\Eloquent\Collection
    {
        $namespace = trim(config('laravel-modular.namespace', 'Modules'), '\\');
        $factoryClass = $namespace . '\\' . $module . '\\Database\\Factories\\' . $model . 'Factory';

        if (! class_exists($factoryClass)) {
            throw new \InvalidArgumentException("Factory [{$factoryClass}] not found.");
        }

        return $factoryClass::new()->count($count)->make();
    }
}
