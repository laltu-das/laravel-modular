<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Tests\Concerns;

use Laltu\LaravelModular\Tests\Fixtures\Tenancy\FakeTenantResolver;
use Laltu\LaravelModular\Tests\Fixtures\Tenancy\NoBillingTenantVoter;

/**
 * Boots the package with a tenant resolver and a voter that rejects Billing,
 * simulating per-tenant module enablement.
 */
trait WithTenancy
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('laravel-modular.tenant_resolver', FakeTenantResolver::class);
        $app['config']->set('laravel-modular.tenant_voter', NoBillingTenantVoter::class);
    }
}
