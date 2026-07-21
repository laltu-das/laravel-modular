<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Support;

use Laltu\LaravelModular\Contracts\TenantResolver;

/**
 * Thin wrapper around the optionally configured TenantResolver so the rest of
 * the package (and your modules) never needs to check whether a resolver has
 * been bound.
 */
final readonly class CurrentTenant
{
    public function __construct(private ?TenantResolver $resolver)
    {
        //
    }

    public function get(): mixed
    {
        return $this->resolver?->current();
    }

    public function has(): bool
    {
        return $this->resolver !== null && $this->resolver->current() !== null;
    }
}
