<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Console\Command;
use LaravelModular\LaravelModular\Discovery\ModuleRepository;
use LaravelModular\LaravelModular\Support\Module;

final class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List discovered modules';

    public function handle(ModuleRepository $modules): int
    {
        $rows = array_map(
            fn (Module $module): array => [$module->name, $module->path],
            $modules->all(),
        );

        $this->table(['Module', 'Path'], $rows);

        return self::SUCCESS;
    }
}
