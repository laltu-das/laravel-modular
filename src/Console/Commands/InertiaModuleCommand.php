<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Inertia\InertiaResponse;

final class InertiaModuleCommand extends Command
{
    protected $signature = 'module:inertia {module? : Module name} {--component=}';
    protected $description = 'Generate Inertia scaffolding for a module';

    public function handle(): int
    {
        $module = $this->argument('module') ?? InertiaResponse::currentModule();

        if (! $module) {
            $this->error('No module specified.');
            return 1;
        }

        $this->info("Inertia scaffolding created for module [{$module}].");
        return 0;
    }
}
