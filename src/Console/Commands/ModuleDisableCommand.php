<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelModular\LaravelModular\Discovery\ModuleRepository;
use LaravelModular\LaravelModular\Events\ModuleDisabled;
use LaravelModular\LaravelModular\Support\Config;

final class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {name : The module name}';

    protected $description = 'Disable a module without deleting its code';

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

        if ($files->exists($marker)) {
            $this->components->info("Module [{$name}] is already disabled.");

            return self::SUCCESS;
        }

        $files->put($marker, '');
        $modules->flush();
        event(new ModuleDisabled($name));

        $this->components->info("Module [{$name}] disabled successfully.");

        return self::SUCCESS;
    }
}
