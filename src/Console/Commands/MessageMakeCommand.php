<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laltu\Modular\Console\Commands\ModuleAwareGenerator;
use Laltu\Modular\Discovery\ModuleRepository;
use Laltu\Modular\Support\Module;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:message', description: 'Create a new message class for asynchronous communication')]
final class MessageMakeCommand extends Command
{
    use ModuleAwareGenerator;

    protected $signature = 'make:message {name : The name of the message} {--module= : The module to create the message in} {--channel= : The queue channel for the message} {--contract : Create the message in the Contracts directory (public API)}';

    protected $description = 'Create a new message class for asynchronous communication between modules';

    public function __construct(
        private Filesystem $files,
        ModuleRepository $modules,
    ) {
        parent::__construct();
        $this->modules = $modules;
    }

    public function handle(): int
    {
        $module = $this->resolveModule();
        $name = $this->argument('name');
        $channel = $this->option('channel') ?: Str::snake(Str::studly($name));
        $asContract = $this->option('contract');

        $directory = $asContract ? 'Contracts' : 'Messages';
        $namespace = $module->namespace.'\\'.$directory;

        $path = $module->path($directory.'/'.$name.'.php');

        if ($this->files->exists($path)) {
            $this->error("Message [{$name}] already exists in module [{$module->name}].");

            return self::FAILURE;
        }

        $this->files->ensureDirectoryExists($module->path($directory));

        $stub = $this->getStub($asContract);
        $content = str_replace(
            ['{{namespace}}', '{{class}}', '{{channel}}'],
            [$namespace, $name, $channel],
            $stub
        );

        $this->files->put($path, $content);

        $this->info("Message [{$name}] created in module [{$module->name}] at {$directory}/{$name}.php");

        return self::SUCCESS;
    }

    private function getStub(bool $asContract): string
    {
        $stubPath = __DIR__.'/../../stubs/message'.($asContract ? '.contract' : '').'.stub';

        if ($this->files->exists($stubPath)) {
            return $this->files->get($stubPath);
        }

        // Fallback inline stub
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
        public string \$exampleProperty,
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

use Laltu\Modular\Communication\Asynchronous\Message;
use JsonSerializable;

/**
 * Message contract for asynchronous communication (public API).
 */
final readonly class {{class}} implements Message, JsonSerializable
{
    public function __construct(
        // Add your message properties here
        public string \$exampleProperty,
    ) {}

    public function channel(): string
    {
        return '{{channel}}';
    }

    public function jsonSerialize(): array
    {
        return [
            'example_property' => \$this->exampleProperty,
        ];
    }
}
STUB;
    }
}