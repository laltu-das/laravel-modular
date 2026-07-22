<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Foundation\Console\InterfaceMakeCommand as BaseInterfaceMakeCommand;

final class InterfaceMakeCommand extends BaseInterfaceMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Contracts';
    }
}
