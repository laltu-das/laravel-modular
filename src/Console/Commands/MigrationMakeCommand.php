<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand as BaseMigrateMakeCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelModular\LaravelModular\Support\Config;
use Symfony\Component\Console\Input\InputOption;

final class MigrationMakeCommand extends BaseMigrateMakeCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->getDefinition()->addOption(new InputOption('module', null, InputOption::VALUE_REQUIRED, 'Generate inside this module'));
    }

    public function handle(): void
    {
        if (! $this->option('module')) {
            parent::handle();

            return;
        }

        $module = Str::studly(is_string($this->option('module')) ? $this->option('module') : '');
        $base = rtrim(Config::string('laravel-modular.path', base_path('Domains')), '/') . '/' . $module;

        if (! is_dir($base)) {
            throw new InvalidArgumentException("Module [{$module}] does not exist. Run moduler:make-module first.");
        }

        // Override the application database path so the parent command
        // writes the migration file into the module's database/migrations directory.
        $this->laravel->useDatabasePath($base . '/database');

        parent::handle();
    }
}
