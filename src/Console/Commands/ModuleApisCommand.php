<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Communication\Synchronous\ModuleApi;
use Laltu\Modular\Exceptions\ModuleNotFound;

final class ModuleApisCommand extends Command
{
    protected $signature = 'module:apis {--module= : Only list APIs for the given module}';

    protected $description = 'List all public APIs exposed by modules';

    public function __construct(private readonly ModuleApi $moduleApi)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $moduleFilter = $this->option('module');

        if (is_string($moduleFilter) && $moduleFilter !== '') {
            return $this->showModuleApis($moduleFilter);
        }

        return $this->showAllApis();
    }

    private function showAllApis(): int
    {
        $apis = $this->moduleApi->getAllApis();

        if ($apis === []) {
            $this->components->info('No public APIs found.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($apis as $moduleName => $moduleApis) {
            foreach ($moduleApis as $interface => $implementation) {
                $rows[] = [$moduleName, $interface, $implementation];
            }
        }

        $this->table(['Module', 'Interface', 'Implementation'], $rows);

        return self::SUCCESS;
    }

    private function showModuleApis(string $moduleName): int
    {
        try {
            $apis = $this->moduleApi->getModuleApis($moduleName);
        } catch (ModuleNotFound) {
            $this->components->error("Module [{$moduleName}] does not exist or is not enabled.");

            return self::FAILURE;
        }

        if ($apis === []) {
            $this->components->info("Module [{$moduleName}] has no public APIs.");

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($apis as $interface => $implementation) {
            $rows[] = [$interface, $implementation];
        }

        $this->components->info("Public APIs for module [{$moduleName}]:");
        $this->table(['Interface', 'Implementation'], $rows);

        return self::SUCCESS;
    }
}
