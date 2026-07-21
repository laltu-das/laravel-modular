<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Events;

use Laltu\LaravelModular\Support\Module;

final readonly class ModuleBooted
{
    public function __construct(public Module $module, public mixed $tenant = null)
    {
        //
    }
}
