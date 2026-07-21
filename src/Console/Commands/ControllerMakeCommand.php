<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;

final class ControllerMakeCommand extends ControllerMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Http/Controllers';
    }
}
