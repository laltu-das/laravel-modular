<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Communication\Asynchronous\MessageBus;

final class MessageQueuesCommand extends Command
{
    protected $signature = 'module:queues {--connection= : Queue connection name} {--queue= : Specific queue to check}';

    protected $description = 'Show message queue sizes for all modules';

    public function __construct(private readonly MessageBus $messageBus)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $connectionOption = $this->option('connection');
        $queueOption = $this->option('queue');
        $connection = is_string($connectionOption) && $connectionOption !== '' ? $connectionOption : null;

        if (is_string($queueOption) && $queueOption !== '') {
            $size = $this->messageBus->getQueueSize($queueOption, $connection);
            $this->components->info("Queue [{$queueOption}] size: {$size}");

            return self::SUCCESS;
        }

        $sizes = $this->messageBus->getAllQueueSizes($connection);

        if ($sizes === []) {
            $this->components->info('No queue data available. Make sure the queue monitor is configured.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($sizes as $name => $size) {
            $rows[] = [$name, (string) $size];
        }

        $this->table(['Queue', 'Size'], $rows);

        return self::SUCCESS;
    }
}
