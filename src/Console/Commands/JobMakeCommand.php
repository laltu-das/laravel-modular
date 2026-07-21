<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\JobMakeCommand as BaseJobMakeCommand;

final class JobMakeCommand extends BaseJobMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Jobs';
    }
}
