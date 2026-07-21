<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ScopeMakeCommand as BaseScopeMakeCommand;

final class ScopeMakeCommand extends BaseScopeMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Persistence/Scopes';
    }
}
