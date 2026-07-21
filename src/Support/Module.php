<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Support;

final readonly class Module
{
    public function __construct(public string $name, public string $path, public string $namespace, public bool $disabled = false)
    {
        //
    }

    public function has(string $relative): bool
    {
        return file_exists($this->path.'/'.$relative);
    }

    public function path(string $relative = ''): string
    {
        return $this->path.($relative === '' ? '' : '/'.$relative);
    }

    public function class(string $relative): string
    {
        return $this->namespace.'\\'.str_replace('/', '\\', trim($relative, '/'));
    }

    /** @return array<string, mixed> */
    public function manifest(): array
    {
        $file = $this->path('module.php');

        return is_file($file) ? (array) require $file : [];
    }
}
