<?php

declare(strict_types=1);

namespace Laltu\Modular\Contracts;

interface TenantResolver
{
    public function current(): mixed;
}
