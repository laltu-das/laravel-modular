<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Discovery\ModuleRepository;
use Laltu\Modular\Support\Module;

final class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List discovered modules and their status';

    public function handle(ModuleRepository $modules): int
    {
        $rows = array_map(
            fn (Module $module): array => [$module->name, $module->disabled ? 'Disabled' : 'Enabled', $module->path],
            $modules->all(true),
        );

        $this->table(['Module', 'Status', 'Path'], $rows);

        return self::SUCCESS;
    }
}
