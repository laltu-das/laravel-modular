<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'module:queues', description: 'Show message queue sizes for all modules')]
final class MessageQueuesCommand extends Command
{
    public function __construct(
        private MessageBus $messageBus,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $connection = $this->option('connection');
        $queue = $this->option('queue');

        if ($queue !== null) {
            $size = $this->messageBus->getQueueSize($queue, $connection);
            $this->info("Queue [{$queue}] size: {$size}");

            return self::SUCCESS;
        }

        $sizes = $this->messageBus->getAllQueueSizes($connection);

        if ($sizes === []) {
            $this->info('No queue data available. Make sure the queue monitor is configured.');

            return self::SUCCESS;
        }

        $headers = ['Queue', 'Size'];
        $rows = [];

        foreach ($sizes as $name => $size) {
            $rows[] = [$name, (string) $size];
        }

        $this->table($headers, $rows);

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['connection', 'c', \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Queue connection name'],
            ['queue', 'q', \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Specific queue to check'],
        ];
    }
}