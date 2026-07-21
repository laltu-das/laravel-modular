<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, \LaravelModular\LaravelModular\Support\Module> modules()
 * @method static \LaravelModular\LaravelModular\Support\Module module(string $name)
 * @method static bool has(string $name)
 * @method static list<string> moduleNames()
 * @method static bool isEnabled(\LaravelModular\LaravelModular\Support\Module $module)
 * @method static mixed tenant()
 * @method static array<int, mixed>|null publish(object $event)
 * @method static void listen(string|array<int, string> $events, \Closure|string|null $listener = null)
 *
 * @see \LaravelModular\LaravelModular\LaravelModular
 */
class LaravelModular extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelModular\LaravelModular\LaravelModular::class;
    }
}
