<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Communication\Synchronous\ModuleApi;
use Laltu\Modular\LaravelModular;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'module:apis', description: 'List all public APIs exposed by modules')]
final class ModuleApisCommand extends Command
{
    public function __construct(
        private ModuleApi $moduleApi,
        private LaravelModular $modular,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $moduleFilter = $this->option('module');

        if ($moduleFilter !== null) {
            return $this->showModuleApis($moduleFilter);
        }

        return $this->showAllApis();
    }

    private function showAllApis(): int
    {
        $apis = $this->moduleApi->getAllApis();

        if ($apis === []) {
            $this->info('No public APIs found.');

            return self::SUCCESS;
        }

        $headers = ['Module', 'Interface', 'Implementation'];
        $rows = [];

        foreach ($apis as $moduleName => $moduleApis) {
            foreach ($moduleApis as $interface => $implementation) {
                $rows[] = [$moduleName, $interface, $implementation];
            }
        }

        $this->table($headers, $rows);

        return self::SUCCESS;
    }

    private function showModuleApis(string $moduleName): int
    {
        try {
            $apis = $this->moduleApi->getModuleApis($moduleName);
        } catch (\Laltu\Modular\Exceptions\ModuleNotFound) {
            $this->error("Module [{$moduleName}] does not exist or is not enabled.");

            return self::FAILURE;
        }

        if ($apis === []) {
            $this->info("Module [{$moduleName}] has no public APIs.");

            return self::SUCCESS;
        }

        $headers = ['Interface', 'Implementation'];
        $rows = [];

        foreach ($apis as $interface => $implementation) {
            $rows[] = [$interface, $implementation];
        }

        $this->info("Public APIs for module [{$moduleName}]:");
        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}