<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelModular\LaravelModular\Support\Config;
use Symfony\Component\Console\Input\InputOption;

trait ModuleAwareGenerator
{
    protected function configure(): void
    {
        parent::configure();
        $this->getDefinition()->addOption(new InputOption('module', null, InputOption::VALUE_REQUIRED, 'Generate inside this module'));
    }

    protected function qualifyClass(mixed $name): string
    {
        if (! $this->option('module')) {
            return parent::qualifyClass($name);
        }

        $name = ltrim($name, '\\/');
        $module = Str::studly($this->moduleOption());
        $root = trim(Config::string('laravel-modular.namespace', 'Modules'), '\\');
        $directory = str_replace('/', '\\', $this->moduleDirectory());

        return $root.'\\'.$module.'\\'.$directory.'\\'.str_replace('/', '\\', $name);
    }

    protected function getPath(mixed $name): string
    {
        if (! $this->option('module')) {
            return parent::getPath($name);
        }

        $module = Str::studly($this->moduleOption());
        $base = rtrim(Config::string('laravel-modular.path', base_path('Modules')), '/').'/'.$module;

        if (! is_dir($base)) {
            throw new InvalidArgumentException("Module [{$module}] does not exist. Run moduler:make-module first.");
        }

        $prefix = trim(Config::string('laravel-modular.namespace', 'Modules'), '\\').'\\'.$module.'\\';
        $directory = str_replace('/', '\\', $this->moduleDirectory());
        $name = Str::after($name, $prefix.$directory.'\\');

        return $base.'/'.$this->modulePathDirectory().'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function modulePathDirectory(): string
    {
        return $this->moduleDirectory();
    }

    private function moduleOption(): string
    {
        $module = $this->option('module');

        return is_string($module) ? $module : '';
    }

    abstract protected function moduleDirectory(): string;
}
