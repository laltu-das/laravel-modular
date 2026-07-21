<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, \Laltu\LaravelModular\Support\Module> modules()
 * @method static \Laltu\LaravelModular\Support\Module module(string $name)
 * @method static bool has(string $name)
 * @method static list<string> moduleNames()
 * @method static bool isEnabled(\Laltu\LaravelModular\Support\Module $module)
 * @method static mixed tenant()
 * @method static array<int, mixed>|null publish(object $event)
 * @method static void listen(string|array<int, string> $events, \Closure|string|null $listener = null)
 *
 * @see \Laltu\LaravelModular\LaravelModular
 */
class LaravelModular extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laltu\LaravelModular\LaravelModular::class;
    }
}
