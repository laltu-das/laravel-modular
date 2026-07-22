<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Str;
use Laltu\Modular\Communication\Asynchronous\Message;
use Laltu\Modular\Communication\Asynchronous\MessageBus;
use Laltu\Modular\Communication\Asynchronous\MessageJob;
use ReflectionClass;

final class MessageConsumeCommand extends Command
{
    protected $signature = 'message:consume
        {--connection= : Queue connection to use (default: configured default)}
        {--queue= : Specific queue to consume (default: all subscribed queues)}
        {--timeout=0 : Timeout in seconds (0 = infinite)}
        {--memory=128 : Memory limit in MB (0 = unlimited)}
        {--sleep=3 : Seconds to sleep when no job is available}';

    protected $description = 'Consume messages and dispatch to registered handlers';

    public function __construct(
        private readonly MessageBus $messageBus,
        private readonly QueueFactory $queue,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $connection = $this->nullableStringOption('connection');
        $specificQueue = $this->nullableStringOption('queue');
        $timeout = (int) $this->stringOption('timeout', '0');
        $memoryLimit = (int) $this->stringOption('memory', '128');
        $sleep = (int) $this->stringOption('sleep', '3');

        if ($memoryLimit > 0) {
            ini_set('memory_limit', $memoryLimit.'M');
        }

        $subscriptions = $this->messageBus->getSubscriptions();

        if ($subscriptions === []) {
            $this->components->warn('No message subscriptions registered.');

            return self::SUCCESS;
        }

        $queues = $specificQueue !== null
            ? [$specificQueue]
            : $this->queuesForSubscriptions(array_keys($subscriptions));

        $this->components->info('Starting message consumer...');
        $this->line('Queues: '.implode(', ', $queues));
        $this->line('Connection: '.($connection ?? 'default'));

        $startTime = time();

        while (true) {
            if ($timeout > 0 && (time() - $startTime) >= $timeout) {
                $this->components->info('Timeout reached.');

                return self::SUCCESS;
            }

            $job = $this->popFromQueues($connection, $queues);

            if ($job === null) {
                sleep(max(0, $sleep));

                continue;
            }

            $this->processJob($job, $subscriptions);
        }
    }

    /**
     * @param  array<class-string<Message>, list<class-string|Closure>>  $subscriptions
     */
    private function processJob(Job $job, array $subscriptions): void
    {
        try {
            $messageJob = $this->messageJobFromPayload($job);

            if (! $messageJob instanceof MessageJob) {
                $this->components->warn('Received non-message job, releasing.');
                $job->release();

                return;
            }

            $message = $messageJob->message;
            $messageClass = $message::class;

            if (! isset($subscriptions[$messageClass])) {
                $this->components->warn("No handlers for message [{$messageClass}], deleting.");
                $job->delete();

                return;
            }

            $handlers = $subscriptions[$messageClass];

            foreach ($handlers as $handler) {
                if (is_string($handler) && class_exists($handler)) {
                    $handlerJob = new $handler($message);
                    $this->queue->connection($message->connection())
                        ->pushOn($message->channel(), $handlerJob);
                } elseif ($handler instanceof Closure) {
                    $handler($message);
                }
            }

            $job->delete();
            $this->components->info("Processed [{$messageClass}] -> ".count($handlers).' handler(s)');
        } catch (\Throwable $e) {
            $this->components->error('Job failed: '.$e->getMessage());

            if (method_exists($job, 'fail')) {
                $job->fail($e);

                return;
            }

            $job->release();
        }
    }

    /**
     * @param  list<class-string<Message>>  $messageClasses
     * @return list<string>
     */
    private function queuesForSubscriptions(array $messageClasses): array
    {
        $queues = [];

        foreach ($messageClasses as $messageClass) {
            $queues[] = $this->queueForMessage($messageClass);
        }

        return array_values(array_unique($queues));
    }

    /** @param  class-string<Message>  $messageClass */
    private function queueForMessage(string $messageClass): string
    {
        if (is_subclass_of($messageClass, Message::class)) {
            $reflection = new ReflectionClass($messageClass);
            $constructor = $reflection->getConstructor();

            if ($reflection->isInstantiable() && ($constructor === null || $constructor->getNumberOfRequiredParameters() === 0)) {
                $message = $reflection->newInstance();

                if ($message instanceof Message) {
                    return $message->channel();
                }
            }
        }

        return Str::snake(class_basename($messageClass));
    }

    /** @param  list<string>  $queues */
    private function popFromQueues(?string $connection, array $queues): ?Job
    {
        $queue = $this->queue->connection($connection);

        foreach ($queues as $queueName) {
            $job = $queue->pop($queueName);

            if ($job instanceof Job) {
                return $job;
            }
        }

        return null;
    }

    private function messageJobFromPayload(Job $job): ?MessageJob
    {
        $payload = json_decode($job->getRawBody(), true);

        if (! is_array($payload)) {
            return null;
        }

        $command = $payload['data']['command'] ?? null;

        if (is_string($command)) {
            $unserialized = @unserialize($command);

            return $unserialized instanceof MessageJob ? $unserialized : null;
        }

        $rawJob = $payload['job'] ?? null;

        return $rawJob instanceof MessageJob ? $rawJob : null;
    }

    private function stringOption(string $name, string $default): string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function nullableStringOption(string $name): ?string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
