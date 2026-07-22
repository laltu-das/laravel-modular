<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Database\Console\Seeds\SeederMakeCommand as BaseSeederMakeCommand;

final class SeederMakeCommand extends BaseSeederMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Database/Seeders';
    }

    protected function modulePathDirectory(): string
    {
        return 'database/seeders';
    }
}
