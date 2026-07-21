<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\JobMakeCommand;

final class JobMakeCommand extends JobMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Jobs';
    }
}
