<?php

declare(strict_types=1);

namespace Laltu\Modular\Tests\Fixtures\Tenancy;

use Laltu\Modular\Contracts\TenantModuleVoter;
use Laltu\Modular\Support\Module;

final class NoBillingTenantVoter implements TenantModuleVoter
{
    public function allows(Module $module, mixed $tenant): bool
    {
        return $module->name !== 'Billing';
    }
}
