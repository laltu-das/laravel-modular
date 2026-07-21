<?php

declare(strict_types=1);
namespace LaravelModular\LaravelModular\Console\Commands;
final class JobMakeCommand extends \Illuminate\Foundation\Console\JobMakeCommand
{
    use ModuleAwareGenerator;
    protected function moduleDirectory(): string { return 'Infrastructure/Jobs'; }
}
