<?php

declare(strict_types=1);
namespace LaravelModular\LaravelModular\Console\Commands;
final class ControllerMakeCommand extends \Illuminate\Routing\Console\ControllerMakeCommand
{
    use ModuleAwareGenerator;
    protected function moduleDirectory(): string { return 'Infrastructure/Http/Controllers'; }
}
