<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ObserverMakeCommand as BaseObserverMakeCommand;

final class ObserverMakeCommand extends BaseObserverMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Observers';
    }
}
