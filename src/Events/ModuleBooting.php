<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Events;

use LaravelModular\LaravelModular\Support\Module;

final readonly class ModuleBooting
{
    public function __construct(public Module $module, public mixed $tenant = null)
    {
        //
    }
}
