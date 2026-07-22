<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laltu\Modular\Support\Config;

final class MakeModuleCommand extends Command
{
    protected $signature = 'module:make {name : The module name} {--force : Overwrite an existing module}';

    protected $description = 'Scaffold a new module (aliases: make:module, moduler:make-module)';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();

        $this->setAliases(['make:module', 'moduler:make-module']);
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
        $this->files->put($root.'/config/'.Str::kebab($name).'.php', $this->moduleConfig());
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
    // Extra event listeners that are not covered by Listeners/ auto-discovery.
    // Wildcards such as {{ namespace }}\Events\* are supported.
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

    private function moduleConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

// Merged into Laravel's config repository under this file's name
// (e.g. a file named billing.php is read via config('billing.option')).
return [
];
PHP.PHP_EOL;
    }

    /** @return list<string> */
    private function directories(): array
    {
        return [
            'Broadcasting',
            'Casts',
            'config',
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
            'Messages',
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
