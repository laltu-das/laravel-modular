<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use LaravelModular\LaravelModular\Support\Config;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final class MakeModuleCommand extends Command
{
    protected $signature = 'moduler:make-module {name : The module name} {--force : Overwrite an existing module}';
    protected $description = 'Create a DDD-ready modular monolith module';

    public function __construct(private readonly Filesystem $files) { parent::__construct(); }

    public function handle(): int
    {
        $argument = $this->argument('name');
        $name = Str::studly(is_string($argument) ? $argument : '');
        $root = rtrim(Config::string('laravel-modular.path', base_path('Domains')), '/').'/'.$name;
        if ($this->files->isDirectory($root) && ! $this->option('force')) {
            $this->components->error("Module [{$name}] already exists."); return self::FAILURE;
        }
        foreach (['Application/Commands', 'Application/Queries', 'Application/Listeners', 'Domain/Entities', 'Domain/Events', 'Domain/Services', 'Domain/ValueObjects', 'Infrastructure/Http/Controllers', 'Infrastructure/Http/Requests', 'Infrastructure/Jobs', 'Infrastructure/Persistence/Models', 'Infrastructure/Providers', 'Contracts', 'database/migrations', 'database/factories', 'database/seeders', 'routes', 'resources/views', 'resources/lang'] as $directory) $this->files->ensureDirectoryExists($root.'/'.$directory);
        $namespace = trim(Config::string('laravel-modular.namespace', 'Domains'), '\\').'\\'.$name;
        $provider = <<<'PHP'
<?php

declare(strict_types=1);

namespace {{ namespace }}\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

final class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void {}
}
PHP;
        $this->files->put($root.'/Infrastructure/Providers/ModuleServiceProvider.php', str_replace('{{ namespace }}', $namespace, $provider));
        $this->files->put($root.'/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n    'name' => '{$name}',\n    'providers' => [{$namespace}\\Infrastructure\\Providers\\ModuleServiceProvider::class],\n    'listeners' => [],\n];\n");
        $this->files->put($root.'/routes/web.php', "<?php\n\ndeclare(strict_types=1);\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// Route::get('/', ...);\n");
        $this->files->put($root.'/routes/api.php', "<?php\n\ndeclare(strict_types=1);\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        $this->components->info("Module [{$name}] created successfully.");
        return self::SUCCESS;
    }
}
