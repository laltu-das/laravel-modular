<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Foundation\Console\ResourceMakeCommand as BaseResourceMakeCommand;

final class ResourceMakeCommand extends BaseResourceMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Http/Resources';
    }
}
