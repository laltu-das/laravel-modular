<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ViewMakeCommand as BaseViewMakeCommand;

final class ViewMakeCommand extends BaseViewMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'resources/views';
    }
}
