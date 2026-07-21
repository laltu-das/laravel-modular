<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laltu\LaravelModular\Boundaries\ModuleBoundaryInspector;
use Laltu\LaravelModular\Discovery\ModuleRepository;
use Laltu\LaravelModular\Support\Config;
use Laltu\LaravelModular\Support\Module;

final class ModuleBoundariesCommand extends Command
{
    protected $signature = 'module:boundaries {--module= : Only inspect the given module}';

    protected $description = 'Report cross-module references that break module boundaries (Spring Modulith style verification)';

    public function handle(ModuleBoundaryInspector $inspector, Filesystem $files): int
    {
        $path = Config::string('laravel-modular.path', base_path('Modules'));
        $namespace = trim(Config::string('laravel-modular.namespace', 'Modules'), '\\');
        $modules = (new ModuleRepository($files, $path, $namespace))->all();

        $only = $this->option('module');

        if (is_string($only) && $only !== '') {
            $modules = array_filter($modules, fn (Module $module): bool => strcasecmp($module->name, Str::studly($only)) === 0);

            if ($modules === []) {
                $this->components->error("Module [{$only}] does not exist or is disabled.");

                return self::FAILURE;
            }
        }

        $violations = $inspector->inspect(
            array_values($modules),
            $namespace,
            Config::stringList('laravel-modular.public_directories', ['Contracts', 'Events', 'Enums']),
        );

        if ($violations === []) {
            $this->components->info('All module boundaries respected.');

            return self::SUCCESS;
        }

        $this->components->error(sprintf('Module boundary violations detected (%d):', count($violations)));

        $this->table(
            ['Module', 'File', 'References'],
            array_map(
                fn (array $violation): array => [$violation['module'], $violation['file'], $violation['referenced']],
                $violations,
            ),
        );

        return self::FAILURE;
    }
}
