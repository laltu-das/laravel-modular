<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Tests;

use LaravelModular\LaravelModular\LaravelModularServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelModularServiceProvider::class,
        ];
    }
}
