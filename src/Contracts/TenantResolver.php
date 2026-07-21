<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Contracts;

interface TenantResolver
{
    public function current(): mixed;
}
