<?php

declare(strict_types=1);
namespace LaravelModular\LaravelModular\Console\Commands;
final class EventMakeCommand extends \Illuminate\Foundation\Console\EventMakeCommand
{
    use ModuleAwareGenerator;
    protected function moduleDirectory(): string { return 'Domain/Events'; }
}
