<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Tests\Fixtures\Tenancy;

use Laltu\LaravelModular\Contracts\TenantModuleVoter;
use Laltu\LaravelModular\Support\Module;

final class NoBillingTenantVoter implements TenantModuleVoter
{
    public function allows(Module $module, mixed $tenant): bool
    {
        return $module->name !== 'Billing';
    }
}
