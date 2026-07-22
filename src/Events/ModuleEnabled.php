<?php

declare(strict_types=1);

namespace Laltu\Modular\Events;

/**
 * Dispatched by `module:enable` after a module's `.disabled` marker is removed.
 */
final readonly class ModuleEnabled
{
    public function __construct(public string $name)
    {
        //
    }
}
