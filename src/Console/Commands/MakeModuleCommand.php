<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelModular\LaravelModular\Support\Config;

final class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The module name} {--force : Overwrite an existing module}';

    protected $description = 'Create a Laravel-style module';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $argument = $this->argument('name');
        $name = Str::studly(is_string($argument) ? $argument : '');
        $root = rtrim(Config::string('laravel-modular.path', base_path('Modules')), '/').'/'.$name;

        if ($this->files->isDirectory($root) && ! $this->option('force')) {
            $this->components->error("Module [{$name}] already exists.");

            return self::FAILURE;
        }

        foreach ($this->directories() as $directory) {
            $this->files->ensureDirectoryExists($root.'/'.$directory);
        }

        $namespace = trim(Config::string('laravel-modular.namespace', 'Modules'), '\\').'\\'.$name;
        $this->files->put($root.'/Providers/ModuleServiceProvider.php', $this->provider($namespace));
        $this->files->put($root.'/module.php', $this->manifest($name, $namespace));
        $this->files->put($root.'/routes/web.php', $this->webRoutes());
        $this->files->put($root.'/routes/api.php', $this->apiRoutes());
        $this->components->info("Module [{$name}] created successfully.");

        return self::SUCCESS;
    }

    private function provider(string $namespace): string
    {
        $provider = <<<'PHP'
<?php

declare(strict_types=1);

namespace {{ namespace }}\Providers;

use Illuminate\Support\ServiceProvider;

final class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
PHP;

        return str_replace('{{ namespace }}', $namespace, $provider).PHP_EOL;
    }

    private function manifest(string $name, string $namespace): string
    {
        $manifest = <<<'PHP'
<?php

declare(strict_types=1);

return [
    'name' => '{{ name }}',
    'providers' => [{{ namespace }}\Providers\ModuleServiceProvider::class],
    'listeners' => [],
];
PHP;

        return str_replace(['{{ name }}', '{{ namespace }}'], [$name, $namespace], $manifest).PHP_EOL;
    }

    private function webRoutes(): string
    {
        $routes = <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Route::get('/', ...);
PHP;

        return $routes.PHP_EOL;
    }

    private function apiRoutes(): string
    {
        $routes = <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
PHP;

        return $routes.PHP_EOL;
    }

    /** @return list<string> */
    private function directories(): array
    {
        return [
            'Broadcasting',
            'Casts',
            'Console/Commands',
            'Contracts',
            'Enums',
            'Events',
            'Exceptions',
            'Http/Controllers',
            'Http/Middleware',
            'Http/Requests',
            'Http/Resources',
            'Jobs',
            'Listeners',
            'Mail',
            'Models',
            'Models/Scopes',
            'Notifications',
            'Observers',
            'Policies',
            'Providers',
            'Rules',
            'View/Components',
            'database/factories',
            'database/migrations',
            'database/seeders',
            'resources/lang',
            'resources/views',
            'routes',
            'tests',
        ];
    }
}
