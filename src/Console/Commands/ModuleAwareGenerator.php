<?php

declare(strict_types=1);
namespace LaravelModular\LaravelModular\Console\Commands;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
trait ModuleAwareGenerator
{
    protected function configure(): void
    {
        parent::configure();
        $this->getDefinition()->addOption(new InputOption('module', null, InputOption::VALUE_REQUIRED, 'Generate inside this module'));
    }

    protected function qualifyClass($name): string
    {
        if (! $this->option('module')) return parent::qualifyClass($name);
        $name = ltrim((string) $name, '\\/');
        $module = Str::studly((string) $this->option('module'));
        $root = trim((string) config('laravel-modular.namespace', 'Domains'), '\\');
        $directory = str_replace('/', '\\', $this->moduleDirectory());
        return $root.'\\'.$module.'\\'.$directory.'\\'.str_replace('/', '\\', $name);
    }

    protected function getPath($name): string
    {
        if (! $this->option('module')) return parent::getPath($name);
        $module = Str::studly((string) $this->option('module'));
        $base = rtrim((string) config('laravel-modular.path'), '/').'/'.$module;
        if (! is_dir($base)) throw new \InvalidArgumentException("Module [{$module}] does not exist. Run moduler:make-module first.");
        $prefix = trim((string) config('laravel-modular.namespace', 'Domains'), '\\').'\\'.$module.'\\';
        return $base.'/'.str_replace('\\', '/', Str::after($name, $prefix)).'.php';
    }

    abstract protected function moduleDirectory(): string;
}
