<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular;

use LaravelModular\LaravelModular\Discovery\ModuleRepository;
use LaravelModular\LaravelModular\Exceptions\ModuleNotFound;
use LaravelModular\LaravelModular\Support\Config;
use LaravelModular\LaravelModular\Support\Module;

final readonly class LaravelModular
{
    public function __construct(private ModuleRepository $modules)
    {
        //
    }

    /** @return array<string, Module> */
    public function modules(): array
    {
        return $this->modules->all();
    }

    public function module(string $name): Module
    {
        return $this->modules->find($name) ?? throw new ModuleNotFound("Module [{$name}] does not exist.");
    }

    public function isPublic(string $class): bool
    {
        $parts = explode('\\', $class);
        $root = trim(Config::string('laravel-modular.namespace', 'Domains'), '\\');

        if ($parts[0] !== $root || count($parts) < 3) {
            return true;
        }

        return in_array($parts[2], (array) config('laravel-modular.public_directories'), true);
    }
}
