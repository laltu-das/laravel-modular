<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Routing\Console\MiddlewareMakeCommand as BaseMiddlewareMakeCommand;

final class MiddlewareMakeCommand extends BaseMiddlewareMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Http/Middleware';
    }
}
