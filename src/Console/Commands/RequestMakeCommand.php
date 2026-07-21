<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

final class RequestMakeCommand extends \Illuminate\Foundation\Console\RequestMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Http/Requests';
    }
}
