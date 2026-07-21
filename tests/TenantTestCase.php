<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Tests;

use LaravelModular\LaravelModular\Tests\Fixtures\Tenancy\FakeTenantResolver;
use LaravelModular\LaravelModular\Tests\Fixtures\Tenancy\NoBillingTenantVoter;

/**
 * Boots the package with a tenant resolver and a voter that rejects Billing,
 * simulating per-tenant module enablement.
 */
abstract class TenantTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('laravel-modular.tenant_resolver', FakeTenantResolver::class);
        $app['config']->set('laravel-modular.tenant_voter', NoBillingTenantVoter::class);
    }
}
