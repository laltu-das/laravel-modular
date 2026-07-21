<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Database\Console\Factories\FactoryMakeCommand as BaseFactoryMakeCommand;

final class FactoryMakeCommand extends BaseFactoryMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Database/Factories';
    }

    protected function modulePathDirectory(): string
    {
        return 'database/factories';
    }
}
