<?php

declare(strict_types=1);

namespace Modules\Catalog\Console\Commands;

use Illuminate\Console\Command;

final class CatalogAboutCommand extends Command
{
    protected $signature = 'catalog:about';

    protected $description = 'Show information about the Catalog module';

    public function handle(): int
    {
        $this->line('Catalog module is active.');

        return self::SUCCESS;
    }
}
