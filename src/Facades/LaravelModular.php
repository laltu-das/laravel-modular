<?php

declare(strict_types=1);

namespace Laltu\Modular\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, \Laltu\Modular\Support\Module> modules()
 * @method static \Laltu\Modular\Support\Module module(string $name)
 * @method static bool has(string $name)
 * @method static list<string> moduleNames()
 * @method static bool isEnabled(\Laltu\Modular\Support\Module $module)
 * @method static mixed tenant()
 * @method static array<int, mixed>|null publish(object $event)
 * @method static void listen(string|array<int, string> $events, \Closure|string|null $listener = null)
 * @method static \Laltu\Modular\Communication\Synchronous\ModuleApi api(string $interface)
 * @method static \Laltu\Modular\Communication\Synchronous\ModuleApi apiFrom(string $interface, string $moduleName)
 * @method static bool hasApi(string $interface)
 * @method static array<string, array<string, string>> allApis()
 * @method static string|null getProviderModule(string $interface)
 * @method static \Laltu\Modular\Communication\Asynchronous\MessageBus messageBus()
 * @method static string publishMessage(\Laltu\Modular\Communication\Asynchronous\Message $message)
 * @method static string publishMessageLater(\Laltu\Modular\Communication\Asynchronous\Message $message, int $delay)
 *
 * @see \Laltu\Modular\LaravelModular
 */
class LaravelModular extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laltu\Modular\LaravelModular::class;
    }
}