<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Foundation\Console\TestMakeCommand as BaseTestMakeCommand;

final class TestMakeCommand extends BaseTestMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'tests';
    }
}
