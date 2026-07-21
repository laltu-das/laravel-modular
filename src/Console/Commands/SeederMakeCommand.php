<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Database\Console\Seeds\SeederMakeCommand as BaseSeederMakeCommand;

final class SeederMakeCommand extends BaseSeederMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'database/seeders';
    }
}
