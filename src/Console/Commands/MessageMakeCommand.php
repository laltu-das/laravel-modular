<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laltu\Modular\Support\Config;
use Laltu\Modular\Support\Module;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:message', description: 'Create a new message class for asynchronous communication')]
final class MessageMakeCommand extends Command
{
    protected $signature = 'make:message {name : The name of the message} {--module= : The module to create the message in} {--channel= : The queue channel for the message} {--contract : Create the message in the Contracts directory (public API)}';

    protected $description = 'Create a new message class for asynchronous communication between modules';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $module = $this->resolveModule();

        if ($module === null) {
            return self::FAILURE;
        }

        $argument = $this->argument('name');
        $name = Str::studly(is_string($argument) ? $argument : '');
        $channelOption = $this->option('channel');
        $channel = is_string($channelOption) && $channelOption !== ''
            ? $channelOption
            : Str::snake($name);
        $asContract = (bool) $this->option('contract');

        $directory = $asContract ? 'Contracts' : 'Messages';
        $namespace = $module->namespace.'\\'.$directory;
        $path = $module->path($directory.'/'.$name.'.php');

        if ($this->files->exists($path)) {
            $this->components->error("Message [{$name}] already exists in module [{$module->name}].");

            return self::FAILURE;
        }

        $this->files->ensureDirectoryExists($module->path($directory));

        $content = str_replace(
            ['{{namespace}}', '{{class}}', '{{channel}}'],
            [$namespace, $name, $channel],
            $this->getStub($asContract),
        );

        $this->files->put($path, $content);

        $this->components->info("Message [{$name}] created in module [{$module->name}] at {$directory}/{$name}.php");

        return self::SUCCESS;
    }

    private function resolveModule(): ?Module
    {
        $moduleOption = $this->option('module');

        if (! is_string($moduleOption) || trim($moduleOption) === '') {
            $this->components->error('The --module option is required.');

            return null;
        }

        $name = Str::studly($moduleOption);
        $root = rtrim(Config::string('laravel-modular.path', base_path('Modules')), '/').'/'.$name;

        if (! $this->files->isDirectory($root)) {
            $this->components->error("Module [{$name}] does not exist. Run make:module first.");

            return null;
        }

        return new Module(
            $name,
            $root,
            trim(Config::string('laravel-modular.namespace', 'Modules'), '\\').'\\'.$name,
        );
    }

    private function getStub(bool $asContract): string
    {
        $stubPath = __DIR__.'/../../stubs/message'.($asContract ? '.contract' : '').'.stub';

        if ($this->files->exists($stubPath)) {
            return $this->files->get($stubPath);
        }

        return $asContract
            ? $this->getContractStub()
            : $this->getDefaultStub();
    }

    private function getDefaultStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Laltu\Modular\Communication\Asynchronous\BaseMessage;

/**
 * Message for asynchronous communication.
 */
final readonly class {{class}} extends BaseMessage
{
    public function __construct(
        // Add your message properties here
        public string $exampleProperty,
    ) {}

    public function channel(): string
    {
        return '{{channel}}';
    }
}
STUB;
    }

    private function getContractStub(): string
    {
        return <<<'STUB'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Laltu\Modular\Communication\Asynchronous\BaseMessage;

/**
 * Message contract for asynchronous communication (public API).
 */
final readonly class {{class}} extends BaseMessage
{
    public function __construct(
        // Add your message properties here
        public string $exampleProperty,
    ) {}

    public function channel(): string
    {
        return '{{channel}}';
    }
}
STUB;
    }
}
