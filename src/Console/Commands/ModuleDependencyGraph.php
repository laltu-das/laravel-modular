<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Support\ModuleDependency;

final class ModuleDependencyGraph extends Command
{
    protected $signature = 'module:dependencies {module?}';
    protected $description = 'Show module dependency graph';

    public function handle(): int
    {
        $graph = new ModuleDependency();
        $graph->register('Orders', ['Inventory', 'Billing']);
        $graph->register('Inventory', ['Catalog']);
        $graph->register('Billing', ['Catalog']);

        $this->info('Module Dependency Graph:');
        foreach ($graph->graph() as $mod => $deps) {
            $this->line("  {$mod} => " . implode(', ', $deps));
        }

        $this->info('Resolve Order: ' . implode(' -> ', $graph->resolveOrder()));
        return 0;
    }
}
