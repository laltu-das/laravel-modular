<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Support\ModuleMiddleware;

final class ModuleMiddlewareCommand extends Command
{
    protected $signature = 'module:middleware {module} {middleware* : Middleware classes to register}';
    protected $description = 'Register middleware stacks for a module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $middleware = $this->argument('middleware');

        $stack = new ModuleMiddleware();
        $stack->registerStack($module, $middleware);

        $this->info('Registered middleware for module ['.$module.']: '.implode(', ', $middleware));
        return 0;
    }
}
