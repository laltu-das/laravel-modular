<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Console\Commands;

use Illuminate\Support\Str;

final class ModelMakeCommand extends \Illuminate\Foundation\Console\ModelMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Infrastructure/Persistence/Models';
    }

    /**
     * Keep Laravel's model options, but place companion artifacts in the module.
     */
    public function handle()
    {
        if (! $this->option('module')) {
            return parent::handle();
        }

        $migration = (bool) $this->option('migration');
        $factory = (bool) $this->option('factory');
        $seeder = (bool) $this->option('seed');

        // Prevent the framework command from writing companions to the application's
        // global database directory. They are generated below in the module instead.
        $this->input->setOption('migration', false);
        $this->input->setOption('factory', false);
        $this->input->setOption('seed', false);

        $result = parent::handle();

        if ($result === false || $result === self::FAILURE) {
            return $result;
        }

        if ($migration) {
            $this->createModuleMigration();
        }

        if ($factory) {
            $this->createModuleFactory();
        }

        if ($seeder) {
            $this->createModuleSeeder();
        }

        return $result;
    }

    private function createModuleMigration(): void
    {
        $name = $this->modelName();
        $table = Str::snake(Str::pluralStudly($name));
        $directory = $this->modulePath('database/migrations');
        $this->files->ensureDirectoryExists($directory);
        $file = $directory.'/'.date('Y_m_d_His').'_create_'.$table.'_table.php';

        $contents = <<<PHP
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table): void {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;

        $this->files->put($file, $contents.PHP_EOL);
        $this->components->info("Migration [{$file}] created successfully.");
    }

    private function createModuleFactory(): void
    {
        $name = $this->modelName();
        $namespace = $this->moduleNamespace();
        $directory = $this->modulePath('database/factories');
        $this->files->ensureDirectoryExists($directory);
        $file = $directory.'/'.$name.'Factory.php';

        $contents = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\Database\Factories;

use {$namespace}\Infrastructure\Persistence\Models\{$name};
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<{$name}> */
final class {$name}Factory extends Factory
{
    protected \$model = {$name}::class;

    public function definition(): array
    {
        return [];
    }
}
PHP;

        $this->files->put($file, $contents.PHP_EOL);
        $this->components->info("Factory [{$file}] created successfully.");
    }

    private function createModuleSeeder(): void
    {
        $name = $this->modelName();
        $namespace = $this->moduleNamespace();
        $directory = $this->modulePath('database/seeders');
        $this->files->ensureDirectoryExists($directory);
        $file = $directory.'/'.$name.'Seeder.php';

        $contents = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\Database\Seeders;

use {$namespace}\Database\Factories\{$name}Factory;
use Illuminate\Database\Seeder;

final class {$name}Seeder extends Seeder
{
    public function run(): void
    {
        {$name}Factory::new()->count(10)->create();
    }
}
PHP;

        $this->files->put($file, $contents.PHP_EOL);
        $this->components->info("Seeder [{$file}] created successfully.");
    }

    private function modelName(): string
    {
        return class_basename(str_replace('\\', '/', (string) $this->argument('name')));
    }

    private function moduleNamespace(): string
    {
        return trim((string) config('laravel-modular.namespace', 'Domains'), '\\').'\\'.Str::studly((string) $this->option('module'));
    }

    private function modulePath(string $path): string
    {
        return rtrim((string) config('laravel-modular.path'), '/').'/'.Str::studly((string) $this->option('module')).'/'.$path;
    }
}
