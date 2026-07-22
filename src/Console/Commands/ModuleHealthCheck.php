<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Support\ModuleHealth;

final class ModuleHealthCheck extends Command
{
    protected $signature = 'module:health {module?}';
    protected $description = 'Run module health checks';

    public function handle(): int
    {
        $module = $this->argument('module');

        if (! $module) {
            $this->error('Module name is required.');
            return 1;
        }

        $health = new ModuleHealth();
        $results = $health->run($module);

        $this->table(['Check', 'Result'], array_map(
            fn ($k, $v) => [$k, $v ? 'PASS' : 'FAIL'],
            array_keys($results),
            array_values($results)
        ));

        return 0;
    }
}
