<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

final class PolicyMakeCommand extends \Illuminate\Foundation\Console\PolicyMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Application/Policies';
    }
}
