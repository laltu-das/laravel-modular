<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\CastMakeCommand as BaseCastMakeCommand;

final class CastMakeCommand extends BaseCastMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Casts';
    }
}
