<?php

declare(strict_types=1);

namespace Laltu\Modular\Tests\Fixtures\Tenancy;

use Laltu\Modular\Contracts\TenantResolver;

final class FakeTenantResolver implements TenantResolver
{
    public function current(): mixed
    {
        return 123;
    }
}
