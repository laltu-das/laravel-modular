<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laltu\Modular\Discovery\ModuleRepository;
use Laltu\Modular\Events\ModuleEnabled;
use Laltu\Modular\Support\Config;

final class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {name : The module name}';

    protected $description = 'Enable a previously disabled module';

    public function handle(Filesystem $files, ModuleRepository $modules): int
    {
        $argument = $this->argument('name');
        $name = Str::studly(is_string($argument) ? $argument : '');
        $root = rtrim(Config::string('laravel-modular.path', base_path('Modules')), '/').'/'.$name;

        if (! $files->isDirectory($root)) {
            $this->components->error("Module [{$name}] does not exist.");

            return self::FAILURE;
        }

        $marker = $root.'/.disabled';

        if (! $files->exists($marker)) {
            $this->components->info("Module [{$name}] is already enabled.");

            return self::SUCCESS;
        }

        $files->delete($marker);
        $modules->flush();
        event(new ModuleEnabled($name));

        $this->components->info("Module [{$name}] enabled successfully.");

        return self::SUCCESS;
    }
}
