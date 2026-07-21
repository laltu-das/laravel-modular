<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Tests;

use Laltu\LaravelModular\LaravelModularServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelModularServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('laravel-modular.path', __DIR__.'/Fixtures/Modules');
        $app['config']->set('laravel-modular.namespace', 'Modules');
    }
}
