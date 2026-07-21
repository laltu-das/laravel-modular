<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular;

use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use LaravelModular\LaravelModular\Console\Commands\CastMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ChannelMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ComponentMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ConsoleMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ControllerMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\EnumMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\EventMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ExceptionMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\FactoryMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\InterfaceMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\JobMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\LaravelModularCommand;
use LaravelModular\LaravelModular\Console\Commands\ListenerMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\MailMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\MakeModuleCommand;
use LaravelModular\LaravelModular\Console\Commands\MiddlewareMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\MigrationMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ModelMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ModuleListCommand;
use LaravelModular\LaravelModular\Console\Commands\NotificationMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ObserverMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\PolicyMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ProviderMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\RequestMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ResourceMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\RuleMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ScopeMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\SeederMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\TestMakeCommand;
use LaravelModular\LaravelModular\Console\Commands\ViewMakeCommand;
use LaravelModular\LaravelModular\Contracts\ModuleEventBus;
use LaravelModular\LaravelModular\Contracts\TenantResolver;
use LaravelModular\LaravelModular\Discovery\ModuleRepository;
use LaravelModular\LaravelModular\Events\ModuleBooted;
use LaravelModular\LaravelModular\Events\ModuleBooting;
use LaravelModular\LaravelModular\Support\Config;
use LaravelModular\LaravelModular\Support\LaravelEventBus;

final class LaravelModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-modular.php', 'laravel-modular');

        foreach (ClassLoader::getRegisteredLoaders() as $loader) {
            $loader->addPsr4(trim(Config::string('laravel-modular.namespace', 'Domains'), '\\').'\\', rtrim(Config::string('laravel-modular.path', base_path('Domains')), '/').'/', true);
        }

        $this->app->singleton(ModuleRepository::class, fn (): ModuleRepository => new ModuleRepository(
            $this->app->make(Filesystem::class),
            Config::string('laravel-modular.path', base_path('Domains')),
            Config::string('laravel-modular.namespace', 'Domains'),
        ));
        $this->app->singleton(LaravelModular::class);
        $this->app->singleton(ModuleEventBus::class, LaravelEventBus::class);
        $resolver = config('laravel-modular.tenant_resolver');

        if (is_string($resolver) && $resolver !== '') {
            $this->app->bind(TenantResolver::class, $resolver);
        }
    }

    public function boot(ModuleRepository $repository): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-modular');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-modular');

        if ((bool) config('laravel-modular.enabled', true)) {
            foreach ($repository->all() as $module) {
                foreach (ClassLoader::getRegisteredLoaders() as $loader) {
                    $loader->addPsr4($module->namespace.'\\Database\\Factories\\', $module->path('database/factories').'/', true);
                    $loader->addPsr4($module->namespace.'\\Database\\Seeders\\', $module->path('database/seeders').'/', true);
                }

                $tenant = $this->app->bound(TenantResolver::class) ? $this->app->make(TenantResolver::class)->current() : null;
                event(new ModuleBooting($module, $tenant));
                $manifest = $module->manifest();

                foreach ((array) ($manifest['providers'] ?? []) as $provider) {
                    if (is_string($provider)) {
                        $this->app->register($provider);
                    }
                }

                if ($this->app->runningInConsole() && isset($manifest['commands'])) {
                    $this->commands((array) $manifest['commands']);
                }

                foreach ((array) ($manifest['listeners'] ?? []) as $event => $listeners) {
                    if (! is_string($event)) {
                        continue;
                    }

                    foreach ((array) $listeners as $listener) {
                        if (is_string($listener) && class_exists($listener)) {
                            $this->app->make(Dispatcher::class)->listen($event, $listener);
                        }
                    }
                }

                if ($module->has('routes/web.php')) {
                    $this->loadRoutesFrom($module->path('routes/web.php'));
                }

                if ($module->has('routes/api.php')) {
                    $this->loadRoutesFrom($module->path('routes/api.php'));
                }

                if ($module->has('database/migrations')) {
                    $this->loadMigrationsFrom($module->path('database/migrations'));
                }

                if ($module->has('resources/views')) {
                    $this->loadViewsFrom($module->path('resources/views'), strtolower($module->name));
                }

                if ($module->has('resources/lang')) {
                    $this->loadTranslationsFrom($module->path('resources/lang'), strtolower($module->name));
                }

                event(new ModuleBooted($module, $tenant));
            }
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([__DIR__.'/../config/laravel-modular.php' => config_path('laravel-modular.php')], ['laravel-modular', 'laravel-modular-config']);
        $this->commands([
            LaravelModularCommand::class,
            MakeModuleCommand::class,
            ModuleListCommand::class,
            CastMakeCommand::class,
            ChannelMakeCommand::class,
            ComponentMakeCommand::class,
            ConsoleMakeCommand::class,
            ControllerMakeCommand::class,
            EnumMakeCommand::class,
            EventMakeCommand::class,
            ExceptionMakeCommand::class,
            FactoryMakeCommand::class,
            InterfaceMakeCommand::class,
            JobMakeCommand::class,
            ListenerMakeCommand::class,
            MailMakeCommand::class,
            MiddlewareMakeCommand::class,
            MigrationMakeCommand::class,
            ModelMakeCommand::class,
            NotificationMakeCommand::class,
            ObserverMakeCommand::class,
            PolicyMakeCommand::class,
            ProviderMakeCommand::class,
            RequestMakeCommand::class,
            ResourceMakeCommand::class,
            RuleMakeCommand::class,
            ScopeMakeCommand::class,
            SeederMakeCommand::class,
            TestMakeCommand::class,
            ViewMakeCommand::class,
        ]);
    }
}
