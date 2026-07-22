<?php

declare(strict_types=1);

namespace Laltu\Modular\Events;

use Laltu\Modular\Support\Module;

final readonly class ModuleBooting
{
    public function __construct(public Module $module, public mixed $tenant = null)
    {
        //
    }
}
