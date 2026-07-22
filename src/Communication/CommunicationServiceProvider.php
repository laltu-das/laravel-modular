<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication;

use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Queue\Monitor;
use Illuminate\Support\ServiceProvider;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Laltu\Modular\Communication\Synchronous\ModuleApi;
use Laltu\Modular\Discovery\ModuleRepository;
use Laltu\Modular\LaravelModular;

final class CommunicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleApi::class, function ($app) {
            return new ModuleApi(
                $app,
                $app->make(ModuleRepository::class),
                $app->make(LaravelModular::class),
            );
        });

        $this->app->singleton(MessageBus::class, function ($app) {
            return new MessageBus(
                $app->make(QueueFactory::class),
                $app->bound(Monitor::class) ? $app->make(Monitor::class) : null,
            );
        });
    }

    public function boot(): void
    {
        // Register macro on LaravelModular facade for synchronous API access
        \Laltu\Modular\Facades\LaravelModular::macro('api', function (string $interface) {
            return $this->getFacadeRoot()->getContainer()->make(ModuleApi::class)
                ->resolve($interface);
        });

        // Register macro for module-specific API access
        \Laltu\Modular\Facades\LaravelModular::macro('apiFrom', function (string $interface, string $moduleName) {
            return $this->getFacadeRoot()->getContainer()->make(ModuleApi::class)
                ->resolveFromModule($interface, $moduleName);
        });

        // Register macro for checking if API exists
        \Laltu\Modular\Facades\LaravelModular::macro('hasApi', function (string $interface) {
            return $this->getFacadeRoot()->getContainer()->make(ModuleApi::class)
                ->has($interface);
        });

        // Register macro for getting all APIs
        \Laltu\Modular\Facades\LaravelModular::macro('allApis', function () {
            return $this->getFacadeRoot()->getContainer()->make(ModuleApi::class)
                ->getAllApis();
        });

        // Register macro for MessageBus
        \Laltu\Modular\Facades\LaravelModular::macro('messageBus', function () {
            return $this->getFacadeRoot()->getContainer()->make(MessageBus::class);
        });

        // Register macro for publishing messages
        \Laltu\Modular\Facades\LaravelModular::macro('publishMessage', function (\Laltu\Modular\Communication\Asynchronous\Message $message) {
            return $this->getFacadeRoot()->getContainer()->make(MessageBus::class)
                ->publish($message);
        });

        // Register macro for publishing messages with delay
        \Laltu\Modular\Facades\LaravelModular::macro('publishMessageLater', function (\Laltu\Modular\Communication\Asynchronous\Message $message, int $delay) {
            return $this->getFacadeRoot()->getContainer()->make(MessageBus::class)
                ->publishLater($message, $delay);
        });
    }
}