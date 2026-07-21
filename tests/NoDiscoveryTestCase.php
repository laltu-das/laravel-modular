<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Tests;

/**
 * Boots the package with parts of auto-discovery switched off.
 */
abstract class NoDiscoveryTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('laravel-modular.auto_discovery.providers', false);
        $app['config']->set('laravel-modular.auto_discovery.listeners', false);
        $app['config']->set('laravel-modular.auto_discovery.routes', false);
    }
}
