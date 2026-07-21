<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Tests\Fixtures\Tenancy;

use LaravelModular\LaravelModular\Contracts\TenantModuleVoter;
use LaravelModular\LaravelModular\Support\Module;

final class NoBillingTenantVoter implements TenantModuleVoter
{
    public function allows(Module $module, mixed $tenant): bool
    {
        return $module->name !== 'Billing';
    }
}
