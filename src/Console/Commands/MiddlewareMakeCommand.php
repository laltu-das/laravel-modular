<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\MiddlewareMakeCommand as BaseMiddlewareMakeCommand;

final class MiddlewareMakeCommand extends BaseMiddlewareMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Http/Middleware';
    }
}
