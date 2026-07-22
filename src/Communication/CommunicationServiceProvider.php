<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Laltu\Modular\Communication\Synchronous\ModuleApi;
use Laltu\Modular\LaravelModular;

final class CommunicationServiceProvider extends ServiceProvider
{
    private const QUEUE_MONITOR_CONTRACT = 'Illuminate\\Contracts\\Queue\\Monitor';

    public function register(): void
    {
        $this->app->singleton(ModuleApi::class, function (Application $app): ModuleApi {
            $modular = $app->make(LaravelModular::class);

            if (! $modular instanceof LaravelModular) {
                throw new \LogicException('The Laravel Modular service is not registered correctly.');
            }

            return new ModuleApi($app, $modular);
        });

        $this->app->singleton(MessageBus::class, function (Application $app): MessageBus {
            $monitor = $app->bound(self::QUEUE_MONITOR_CONTRACT)
                ? $app->make(self::QUEUE_MONITOR_CONTRACT)
                : null;

            return new MessageBus(
                $app,
                is_object($monitor) ? $monitor : null,
            );
        });
    }
}
