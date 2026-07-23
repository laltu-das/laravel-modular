<?php

declare(strict_types=1);

namespace Laltu\Modular\Facades;

use Illuminate\Support\Facades\Facade;
use Laltu\Modular\Communication\Asynchronous\Message;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Laltu\Modular\Support\Module;

/**
 * @method static array<string, Module> modules()
 * @method static Module module(string $name)
 * @method static bool has(string $name)
 * @method static list<string> moduleNames()
 * @method static bool isEnabled(Module $module)
 * @method static mixed tenant()
 * @method static array<int, mixed>|null publish(object $event)
 * @method static void listen(string|array<int, string> $events, \Closure|string|null $listener = null)
 * @method static object api(string $interface)
 * @method static object apiFrom(string $interface, string $moduleName)
 * @method static bool hasApi(string $interface)
 * @method static array<string, array<string, string>> allApis()
 * @method static string|null getProviderModule(string $interface)
 * @method static MessageBus messageBus()
 * @method static string publishMessage(Message $message)
 * @method static string publishMessageLater(Message $message, int $delay)
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
