<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Console\Command;

class LaravelModularCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'laravel-modular:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package laravel-modular.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('LaravelModular placeholder command executed.');

        return self::SUCCESS;
    }
}
