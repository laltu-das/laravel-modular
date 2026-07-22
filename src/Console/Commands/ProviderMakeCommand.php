<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Foundation\Console\ProviderMakeCommand as BaseProviderMakeCommand;

final class ProviderMakeCommand extends BaseProviderMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Providers';
    }
}
