<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\EnumMakeCommand as BaseEnumMakeCommand;

final class EnumMakeCommand extends BaseEnumMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Enums';
    }
}
