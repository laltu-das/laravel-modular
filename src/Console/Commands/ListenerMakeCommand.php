<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ListenerMakeCommand as BaseListenerMakeCommand;

final class ListenerMakeCommand extends BaseListenerMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Application/Listeners';
    }
}
