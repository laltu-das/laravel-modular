<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ComponentMakeCommand as BaseComponentMakeCommand;

final class ComponentMakeCommand extends BaseComponentMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/View/Components';
    }
}
