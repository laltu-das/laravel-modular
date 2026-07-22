<?php

declare(strict_types=1);

namespace Laltu\Modular\Events;

/**
 * Dispatched by `module:disable` after a module's `.disabled` marker is created.
 */
final readonly class ModuleDisabled
{
    public function __construct(public string $name)
    {
        //
    }
}
