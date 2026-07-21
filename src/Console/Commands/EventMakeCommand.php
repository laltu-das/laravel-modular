<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\EventMakeCommand as BaseEventMakeCommand;

final class EventMakeCommand extends BaseEventMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Domain/Events';
    }
}
