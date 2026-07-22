<?php

declare(strict_types=1);

namespace Laltu\Modular;

use Composer\Autoload\ClassLoader;
use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laltu\Modular\Boundaries\ModuleBoundaryInspector;
use Laltu\Modular\Communication\CommunicationServiceProvider;
use Laltu\Modular\Console\Commands\CastMakeCommand;
use Laltu\Modular\Console\Commands\ChannelMakeCommand;
use Laltu\Modular\Console\Commands\ComponentMakeCommand;
use Laltu\Modular\Console\Commands\ConsoleMakeCommand;
use Laltu\Modular\Console\Commands\ControllerMakeCommand;
use Laltu\Modular\Console\Commands\EnumMakeCommand;
use Laltu\Modular\Console\Commands\EventMakeCommand;
use Laltu\Modular\Console\Commands\ExceptionMakeCommand;
use Laltu\Modular\Console\Commands\FactoryMakeCommand;
use Laltu\Modular\Console\Commands\InterfaceMakeCommand;
use Laltu\Modular\Console\Commands\JobMakeCommand;
use Laltu\Modular\Console\Commands\LaravelModularCommand;
use Laltu\Modular\Console\Commands\ListenerMakeCommand;
use Laltu\Modular\Console\Commands\MailMakeCommand;
use Laltu\Modular\Console\Commands\MakeModuleCommand;
use Laltu\Modular\Console\Commands\MiddlewareMakeCommand;
use Laltu\Modular\Console\Commands\ModelMakeCommand;
use Laltu\Modular\Console\Commands\MessageConsumeCommand;
use Laltu\Modular\Console\Commands\MessageMakeCommand;
use Laltu\Modular\Console\Commands\MessageQueuesCommand;
use Laltu\Modular\Console\Commands\ModuleApisCommand;
use Laltu\Modular\Console\Commands\ModuleBoundariesCommand;
use Laltu\Modular\Console\Commands\ModuleDisableCommand;
use Laltu\Modular\Console\Commands\ModuleEnableCommand;
use Laltu\Modular\Console\Commands\ModuleListCommand;
use Laltu\Modular\Console\Commands\NotificationMakeCommand;
use Laltu\Modular\Console\Commands\ObserverMakeCommand;
use Laltu\Modular\Console\Commands\PolicyMakeCommand;
use Laltu\Modular\Console\Commands\ProviderMakeCommand;
use Laltu\Modular\Console\Commands\RequestMakeCommand;
use Laltu\Modular\Console\Commands\ResourceMakeCommand;
use Laltu\Modular\Console\Commands\RuleMakeCommand;
use Laltu\Modular\Console\Commands\ScopeMakeCommand;
use Laltu\Modular\Console\Commands\SeederMakeCommand;
use Laltu\Modular\Console\Commands\TestMakeCommand;
use Laltu\Modular\Console\Commands\ViewMakeCommand;
use Laltu\Modular\Contracts\TenantModuleVoter;
use Laltu\Modular\Contracts\TenantResolver;
use Laltu\Modular\Discovery\ListenerDiscovery;
use Laltu\Modular\Discovery\ModuleClassDiscovery;
use Laltu\Modular\Discovery\ModuleRepository;
use Laltu\Modular\Events\ModuleBooted;
use Laltu\Modular\Events\ModuleBooting;
use Laltu\Modular\Support\Config;
use Laltu\Modular\Support\CurrentTenant;
use Laltu\Modular\Support\Module;

final class LaravelModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-modular.php', 'laravel-modular');

        foreach (ClassLoader::getRegisteredLoaders() as $loader) {
            $loader->addPsr4(trim(Config::string('laravel-modular.namespace', 'Modules'), '\\').'\\', rtrim(Config::string('laravel-modular.path', base_path('Modules')), '/').'/', true);
        }

        $this->app->singleton(ModuleRepository::class, fn (): ModuleRepository => new ModuleRepository(
            $this->app->make(Filesystem::class),
            Config::string('laravel-modular.path', base_path('Modules')),
            Config::string('laravel-modular.namespace', 'Modules'),
        ));

        $resolver = config('laravel-modular.tenant_resolver');

        if (is_string($resolver) && $resolver !== '') {
            $this->app->bind(TenantResolver::class, $resolver);
        }

        $voter = config('laravel-modular.tenant_voter');

        if (is_string($voter) && $voter !== '') {
            $this->app->bind(TenantModuleVoter::class, $voter);
        }

        $this->app->singleton(CurrentTenant::class, fn (Application $app): CurrentTenant => new CurrentTenant(
            $app->bound(TenantResolver::class) ? $app->make(TenantResolver::class) : null,
        ));

        $this->app->singleton(ModuleClassDiscovery::class, fn (Application $app): ModuleClassDiscovery => new ModuleClassDiscovery(
            $app->make(Filesystem::class),
        ));

        $this->app->singleton(ListenerDiscovery::class, fn (Application $app): ListenerDiscovery => new ListenerDiscovery(
            $app->make(ModuleClassDiscovery::class),
        ));

        $this->app->singleton(ModuleBoundaryInspector::class, fn (Application $app): ModuleBoundaryInspector => new ModuleBoundaryInspector(
            $app->make(Filesystem::class),
        ));

        if (! class_exists(\Laltu\Modular::class, false)) {
            class_alias(LaravelModular::class, \Laltu\Modular::class);
        }

        $this->app->singleton(LaravelModular::class, fn (Application $app): LaravelModular => new LaravelModular(
            $app,
            $app->make(ModuleRepository::class),
            $app->make(CurrentTenant::class),
            $app->bound(TenantModuleVoter::class) ? $app->make(TenantModuleVoter::class) : null,
            $app->make(Dispatcher::class),
        ));

        $this->app->alias(LaravelModular::class, \Laltu\Modular::class);

        // Register communication services (synchronous API + asynchronous messaging)
        $this->app->register(CommunicationServiceProvider::class);

        $this->registerModules($this->app->make(ModuleRepository::class), $this->app->make(LaravelModular::class));
    }

    public function boot(ModuleRepository $repository, LaravelModular $modular): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-modular');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-modular');

        if (Config::bool('laravel-modular.enabled', true)) {
            $tenant = $modular->tenant();

            foreach ($repository->all() as $module) {
                if (! $modular->isEnabled($module)) {
                    continue;
                }

                event(new ModuleBooting($module, $tenant));

                $this->bootModule($module);

                event(new ModuleBooted($module, $tenant));
            }
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([__DIR__.'/../config/laravel-modular.php' => config_path('laravel-modular.php')], ['laravel-modular', 'laravel-modular-config']);

        // Core commands (always available)
        $this->commands([
            LaravelModularCommand::class,
            MakeModuleCommand::class,
            ModuleApisCommand::class,
            MessageQueuesCommand::class,
            MessageConsumeCommand::class,
            ModuleBoundariesCommand::class,
            ModuleDisableCommand::class,
            ModuleEnableCommand::class,
            ModuleListCommand::class,
        ]);

        // Generator commands - only register if the Laravel base class exists
        $generatorCommands = [
            CastMakeCommand::class => 'Illuminate\\Foundation\\Console\\CastMakeCommand',
            ChannelMakeCommand::class => 'Illuminate\\Foundation\\Console\\ChannelMakeCommand',
            ComponentMakeCommand::class => 'Illuminate\\Foundation\\Console\\ComponentMakeCommand',
            ConsoleMakeCommand::class => 'Illuminate\\Foundation\\Console\\ConsoleMakeCommand',
            ControllerMakeCommand::class => 'Illuminate\\Routing\\Console\\ControllerMakeCommand',
            EnumMakeCommand::class => 'Illuminate\\Foundation\\Console\\EnumMakeCommand',
            EventMakeCommand::class => 'Illuminate\\Foundation\\Console\\EventMakeCommand',
            ExceptionMakeCommand::class => 'Illuminate\\Foundation\\Console\\ExceptionMakeCommand',
            FactoryMakeCommand::class => 'Illuminate\\Database\\Console\\Factories\\FactoryMakeCommand',
            InterfaceMakeCommand::class => 'Illuminate\\Foundation\\Console\\InterfaceMakeCommand',
            JobMakeCommand::class => 'Illuminate\\Foundation\\Console\\JobMakeCommand',
            ListenerMakeCommand::class => 'Illuminate\\Foundation\\Console\\ListenerMakeCommand',
            MailMakeCommand::class => 'Illuminate\\Foundation\\Console\\MailMakeCommand',
            MessageMakeCommand::class => 'Illuminate\\Foundation\\Console\\EventMakeCommand',
            MiddlewareMakeCommand::class => 'Illuminate\\Routing\\Console\\MiddlewareMakeCommand',
            ModelMakeCommand::class => 'Illuminate\\Foundation\\Console\\ModelMakeCommand',
            NotificationMakeCommand::class => 'Illuminate\\Foundation\\Console\\NotificationMakeCommand',
            ObserverMakeCommand::class => 'Illuminate\\Foundation\\Console\\ObserverMakeCommand',
            PolicyMakeCommand::class => 'Illuminate\\Foundation\\Console\\PolicyMakeCommand',
            ProviderMakeCommand::class => 'Illuminate\\Foundation\\Console\\ProviderMakeCommand',
            RequestMakeCommand::class => 'Illuminate\\Foundation\\Console\\RequestMakeCommand',
            ResourceMakeCommand::class => 'Illuminate\\Foundation\\Console\\ResourceMakeCommand',
            RuleMakeCommand::class => 'Illuminate\\Foundation\\Console\\RuleMakeCommand',
            ScopeMakeCommand::class => 'Illuminate\\Foundation\\Console\\ScopeMakeCommand',
            SeederMakeCommand::class => 'Illuminate\\Database\\Console\\Seeds\\SeederMakeCommand',
            TestMakeCommand::class => 'Illuminate\\Foundation\\Console\\TestMakeCommand',
            ViewMakeCommand::class => 'Illuminate\\Foundation\\Console\\ViewMakeCommand',
        ];

        $availableCommands = [];
        foreach ($generatorCommands as $command => $baseClass) {
            if (class_exists($baseClass)) {
                $availableCommands[] = $command;
            }
        }

        if ($availableCommands !== []) {
            $this->commands($availableCommands);
        }
    }

    /**
     * Register module providers during the container's registration phase so
     * each module provider enjoys the full lifecycle (register + boot), the
     * same way Spring Boot auto-configurations participate in the context
     * refresh. Providers the tenant voter rejects are skipped entirely.
     */
    private function registerModules(ModuleRepository $repository, LaravelModular $modular): void
    {
        if (! Config::bool('laravel-modular.enabled', true)) {
            return;
        }

        foreach ($repository->all() as $module) {
            $this->registerModuleAutoloaders($module);
        }

        foreach ($repository->all() as $module) {
            if (! $modular->isEnabled($module)) {
                continue;
            }

            $this->registerModuleProviders($module, $module->manifest());
        }
    }

    /**
     * Boot one module like Spring Boot auto-configuration: convention-based
     * registration of config, commands, listeners, policies, observers,
     * routes, migrations, views, and translations.
     */
    private function bootModule(Module $module): void
    {
        $manifest = $module->manifest();

        if (Config::bool('laravel-modular.auto_discovery.config', true)) {
            $this->mergeModuleConfig($module);
        }

        $this->registerModuleCommands($module, $manifest);
        $this->registerModuleListeners($module, $manifest);
        $this->registerModulePolicies($module);
        $this->registerModuleObservers($module);

        if (Config::bool('laravel-modular.auto_discovery.routes', true)) {
            if ($module->has('routes/web.php')) {
                $this->loadRoutesFrom($module->path('routes/web.php'));
            }

            if ($module->has('routes/api.php')) {
                $this->loadRoutesFrom($module->path('routes/api.php'));
            }
        }

        if (Config::bool('laravel-modular.auto_discovery.migrations', true) && $module->has('database/migrations')) {
            $this->loadMigrationsFrom($module->path('database/migrations'));
        }

        if (Config::bool('laravel-modular.auto_discovery.views', true) && $module->has('resources/views')) {
            $this->loadViewsFrom($module->path('resources/views'), strtolower($module->name));
        }

        if (Config::bool('laravel-modular.auto_discovery.translations', true) && $module->has('resources/lang')) {
            $this->loadTranslationsFrom($module->path('resources/lang'), strtolower($module->name));
        }
    }

    private function registerModuleAutoloaders(Module $module): void
    {
        foreach (ClassLoader::getRegisteredLoaders() as $loader) {
            $loader->addPsr4($module->namespace.'\\Database\\Factories\\', $module->path('database/factories').'/', true);
            $loader->addPsr4($module->namespace.'\\Database\\Seeders\\', $module->path('database/seeders').'/', true);
        }
    }

    /**
     * Merge each php file below the module's config/ directory under a key
     * named after the file (config/billing.php => config('billing.*')).
     */
    private function mergeModuleConfig(Module $module): void
    {
        $directory = $module->path('config');

        if (! $this->app->make(Filesystem::class)->isDirectory($directory)) {
            return;
        }

        foreach ($this->app->make(Filesystem::class)->files($directory) as $file) {
            if ($file->getExtension() === 'php') {
                $this->mergeConfigFrom($file->getPathname(), $file->getFilenameWithoutExtension());
            }
        }
    }

    /**
     * Manifest providers first, then any concrete *ServiceProvider below the
     * module's Providers directory (auto-discovered, de-duplicated).
     *
     * @param  array<string, mixed>  $manifest
     */
    private function registerModuleProviders(Module $module, array $manifest): void
    {
        $providers = [];

        foreach ((array) ($manifest['providers'] ?? []) as $provider) {
            if (is_string($provider)) {
                $providers[] = $provider;
            }
        }

        if (Config::bool('laravel-modular.auto_discovery.providers', true)) {
            foreach ($this->classes()->concreteIn($module, 'Providers') as $provider) {
                if (is_subclass_of($provider, ServiceProvider::class)) {
                    $providers[] = $provider;
                }
            }
        }

        foreach (array_unique($providers) as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function registerModuleCommands(Module $module, array $manifest): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $commands = [];

        foreach ((array) ($manifest['commands'] ?? []) as $command) {
            if (is_string($command)) {
                $commands[] = $command;
            }
        }

        if (Config::bool('laravel-modular.auto_discovery.commands', true)) {
            foreach ($this->classes()->concreteIn($module, 'Console/Commands') as $command) {
                if (is_subclass_of($command, Command::class)) {
                    $commands[] = $command;
                }
            }
        }

        $commands = array_values(array_unique($commands));

        if ($commands !== []) {
            $this->commands($commands);
        }
    }

    /**
     * Manifest listeners (including wildcards) plus every listener below the
     * module's Listeners directory, whose handled event is inferred from the
     * type-hint of its handle method.
     *
     * @param  array<string, mixed>  $manifest
     */
    private function registerModuleListeners(Module $module, array $manifest): void
    {
        $listeners = [];

        foreach ((array) ($manifest['listeners'] ?? []) as $event => $handlers) {
            if (is_string($event)) {
                $listeners[$event] = array_values(array_filter((array) $handlers, 'is_string'));
            }
        }

        if (Config::bool('laravel-modular.auto_discovery.listeners', true)) {
            foreach ($this->listeners()->discover($module) as $event => $handlers) {
                $listeners[$event] = array_values(array_unique(array_merge((array) ($listeners[$event] ?? []), $handlers)));
            }
        }

        foreach ($listeners as $event => $handlers) {
            foreach ($handlers as $handler) {
                if (class_exists($handler)) {
                    $this->app->make(Dispatcher::class)->listen($event, $handler);
                }
            }
        }
    }

    /**
     * Policies follow the {Model}Policy => Models/{Model} convention.
     */
    private function registerModulePolicies(Module $module): void
    {
        if (! Config::bool('laravel-modular.auto_discovery.policies', true)) {
            return;
        }

        $policies = [];

        foreach ($this->classes()->concreteIn($module, 'Policies') as $policy) {
            $model = $module->class('Models/'.Str::beforeLast(class_basename($policy), 'Policy'));

            if (class_exists($model)) {
                $policies[$model] = $policy;
            }
        }

        if ($policies !== []) {
            $gate = $this->app->make(Gate::class);

            foreach ($policies as $model => $policy) {
                $gate->policy($model, $policy);
            }
        }
    }

    /**
     * Observers follow the {Model}Observer => Models/{Model} convention.
     */
    private function registerModuleObservers(Module $module): void
    {
        if (! Config::bool('laravel-modular.auto_discovery.observers', true) || ! class_exists(Model::class)) {
            return;
        }

        foreach ($this->classes()->concreteIn($module, 'Observers') as $observer) {
            $model = $module->class('Models/'.Str::beforeLast(class_basename($observer), 'Observer'));

            if (class_exists($model) && is_subclass_of($model, Model::class)) {
                $model::observe($observer);
            }
        }
    }

    private function classes(): ModuleClassDiscovery
    {
        return $this->app->make(ModuleClassDiscovery::class);
    }

    private function listeners(): ListenerDiscovery
    {
        return $this->app->make(ListenerDiscovery::class);
    }
}
