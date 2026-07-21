<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\EventMakeCommand as BaseEventMakeCommand;

final class EventMakeCommand extends BaseEventMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Events';
    }
}
