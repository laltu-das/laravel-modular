<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ChannelMakeCommand as BaseChannelMakeCommand;

final class ChannelMakeCommand extends BaseChannelMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Broadcasting';
    }
}
