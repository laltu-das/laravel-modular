<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\RuleMakeCommand as BaseRuleMakeCommand;

final class RuleMakeCommand extends BaseRuleMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Rules';
    }
}
