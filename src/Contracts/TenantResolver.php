<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Contracts;

interface TenantResolver
{
    public function current(): mixed;
}
