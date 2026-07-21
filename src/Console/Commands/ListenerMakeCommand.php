<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ListenerMakeCommand as BaseListenerMakeCommand;

final class ListenerMakeCommand extends BaseListenerMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Listeners';
    }
}
