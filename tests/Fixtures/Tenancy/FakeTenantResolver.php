<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Tests\Fixtures\Tenancy;

use Laltu\LaravelModular\Contracts\TenantResolver;

final class FakeTenantResolver implements TenantResolver
{
    public function current(): mixed
    {
        return 123;
    }
}
