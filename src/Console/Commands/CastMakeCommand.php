<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\CastMakeCommand as BaseCastMakeCommand;

final class CastMakeCommand extends BaseCastMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Casts';
    }
}
