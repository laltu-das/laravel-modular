<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Queue;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Laltu\Modular\Communication\Asynchronous\MessageJob;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'message:consume', description: 'Consume messages and dispatch to registered handlers')]
final class MessageConsumeCommand extends Command
{
    public function __construct(
        private MessageBus $messageBus,
        private QueueFactory $queue,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Queue connection to use', 'default');
        $this->addOption('queue', 'q', InputOption::VALUE_OPTIONAL, 'Specific queue to consume (default: all subscribed queues)');
        $this->addOption('timeout', 't', InputOption::VALUE_OPTIONAL, 'Timeout in seconds (0 = infinite)', '0');
        $this->addOption('memory', 'm', InputOption::VALUE_OPTIONAL, 'Memory limit in MB (0 = unlimited)', '128');
        $this->addOption('sleep', 's', InputOption::VALUE_OPTIONAL, 'Seconds to sleep when no job is available', '3');
    }

    public function handle(): int
    {
        $connection = $this->option('connection');
        $specificQueue = $this->option('queue');
        $timeout = (int) $this->option('timeout');
        $memoryLimit = (int) $this->option('memory');
        $sleep = (int) $this->option('sleep');

        if ($memoryLimit > 0) {
            ini_set('memory_limit', $memoryLimit.'M');
        }

        $subscriptions = $this->messageBus->getSubscriptions();

        if ($subscriptions === []) {
            $this->warn('No message subscriptions registered.');

            return self::SUCCESS;
        }

        // Determine which queues to listen on
        $queues = $specificQueue !== null
            ? [$specificQueue]
            : array_unique(array_map(
                fn ($messageClass) => (new $messageClass())->channel(),
                array_keys($subscriptions)
            ));

        $this->info('Starting message consumer...');
        $this->info('Queues: '.implode(', ', $queues));
        $this->info('Connection: '.$connection);

        $startTime = time();

        while (true) {
            if ($timeout > 0 && (time() - $startTime) >= $timeout) {
                $this->info('Timeout reached.');

                return self::SUCCESS;
            }

            $job = $this->queue->connection($connection)->pop($queues);

            if ($job === null) {
                if ($this->option('timeout') > 0) {
                    sleep($sleep);
                }

                continue;
            }

            $this->processJob($job, $subscriptions);
        }
    }

    private function processJob(Job $job, array $subscriptions): void
    {
        try {
            $payload = $job->getRawBody();
            $data = json_decode($payload, true);

            if (! isset($data['job']) || ! $data['job'] instanceof MessageJob) {
                $this->warn('Received non-message job, releasing.');
                $job->release();

                return;
            }

            $messageJob = $data['job'];
            $message = $messageJob->message;
            $messageClass = get_class($message);

            if (! isset($subscriptions[$messageClass])) {
                $this->warn("No handlers for message [{$messageClass}], deleting.");
                $job->delete();

                return;
            }

            $handlers = $subscriptions[$messageClass];

            foreach ($handlers as $handler) {
                if (is_string($handler) && class_exists($handler)) {
                    // It's a job class - dispatch it with the message
                    $handlerJob = new $handler($message);
                    $this->queue->connection($message->connection())
                        ->pushOn($message->channel(), $handlerJob);
                } elseif ($handler instanceof \Closure) {
                    // It's a closure - execute it directly
                    $handler($message);
                }
            }

            $job->delete();
            $this->info("Processed [{$messageClass}] -> ".count($handlers).' handler(s)');

        } catch (\Throwable $e) {
            $this->error('Job failed: '.$e->getMessage());
            $job->failed();
        }
    }
}