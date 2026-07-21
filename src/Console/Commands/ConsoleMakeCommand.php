<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ConsoleMakeCommand as BaseConsoleMakeCommand;

final class ConsoleMakeCommand extends BaseConsoleMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Console/Commands';
    }
}
