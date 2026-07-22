<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Support\ModuleCache;

final class ModuleCacheFlushCommand extends Command
{
    protected $signature = 'module:cache-flush {module? : Module name} {--store=}';
    protected $description = 'Flush module-level cache';

    public function handle(): int
    {
        $module = $this->argument('module') ?? 'global';
        $store = $this->option('store');

        $cache = new ModuleCache($store);
        $cache->forModule($module);

        $result = $cache->flushModule();

        if ($result) {
            $this->info("Cache flushed for module [{$module}].");
            return 0;
        }

        $this->warn("Failed to flush cache for module [{$module}].");
        return 1;
    }
}
