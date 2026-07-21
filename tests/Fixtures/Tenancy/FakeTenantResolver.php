<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Tests\Fixtures\Tenancy;

use LaravelModular\LaravelModular\Contracts\TenantResolver;

final class FakeTenantResolver implements TenantResolver
{
    public function current(): mixed
    {
        return 123;
    }
}
