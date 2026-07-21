<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand as BaseControllerMakeCommand;

final class ControllerMakeCommand extends BaseControllerMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Http/Controllers';
    }
}
