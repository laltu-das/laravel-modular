<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ExceptionMakeCommand as BaseExceptionMakeCommand;

final class ExceptionMakeCommand extends BaseExceptionMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Exceptions';
    }
}
