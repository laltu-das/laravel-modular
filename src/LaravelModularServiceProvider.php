<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular;

use Illuminate\Support\ServiceProvider;
use LaravelModular\LaravelModular\Console\Commands\LaravelModularCommand;

class LaravelModularServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-modular.php', 'laravel-modular');

        $this->app->singleton(LaravelModular::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/laravel-modular.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-modular');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-modular');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-modular.php' => config_path('laravel-modular.php'),
        ], ['laravel-modular', 'laravel-modular-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-modular'),
        ], ['laravel-modular', 'laravel-modular-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/laravel-modular'),
        ], ['laravel-modular', 'laravel-modular-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-modular'),
        ], ['laravel-modular', 'laravel-modular-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['laravel-modular', 'laravel-modular-migrations']);

        $this->commands([
            LaravelModularCommand::class,
        ]);
    }
}
