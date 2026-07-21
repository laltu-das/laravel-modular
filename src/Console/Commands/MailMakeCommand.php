<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\MailMakeCommand as BaseMailMakeCommand;

final class MailMakeCommand extends BaseMailMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Mail';
    }
}
