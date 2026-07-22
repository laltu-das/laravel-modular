<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

/**
 * Message bus for asynchronous communication between modules.
 *
 * Uses Laravel's queue system as the message broker.
 * Modules publish messages (fire-and-forget), and other modules
 * consume them via queue workers.
 *
 * Usage:
 * ```php
 * // Publishing a message (Module A)
 * MessageBus::publish(new OrderPlaced('ORD-123', 456, 99.99));
 *
 * // Consuming a message (Module B - in a Job)
 * final class ProcessOrderPlaced implements ShouldQueue
 * {
 *     public function __construct(
 *         public readonly OrderPlaced $message,
 *     ) {}
 *
 *     public function handle(): void
 *     {
 *         // Handle the message
 *     }
 * }
 *
 * // In Module B's service provider:
 * $messageBus->subscribe(OrderPlaced::class, ProcessOrderPlaced::class);
 * ```
 */
final class MessageBus
{
    /**
     * @var array<class-string<Message>, list<class-string<ShouldQueue>|Closure>>
     */
    private array $subscriptions = [];

    public function __construct(
        private QueueFactory|Container $queue,
        private ?object $monitor = null,
    ) {}

    /**
     * Publish a message to the message broker (fire-and-forget).
     *
     * @return string The job ID, or a generated local ID when the queue driver does not return one.
     */
    public function publish(Message $message): string
    {
        $job = new MessageJob($message);

        if ($message->delay() > 0) {
            return $this->publishLater($message, $message->delay());
        }

        $id = $this->queue()->connection($message->connection())
            ->pushOn($message->channel(), $job);

        return $this->normalizeJobId($id);
    }

    /**
     * Publish a message with a delay.
     *
     * @return string The job ID, or a generated local ID when the queue driver does not return one.
     */
    public function publishLater(Message $message, int $delay): string
    {
        $job = new MessageJob($message);

        $id = $this->queue()->connection($message->connection())
            ->laterOn($message->channel(), $delay, $job);

        return $this->normalizeJobId($id);
    }

    /**
     * Publish multiple messages in a batch.
     *
     * @param  iterable<Message>  $messages
     * @return list<string> Job IDs
     */
    public function publishBatch(iterable $messages): array
    {
        $jobIds = [];

        foreach ($messages as $message) {
            $jobIds[] = $this->publish($message);
        }

        return $jobIds;
    }

    /**
     * Subscribe a job class or closure to handle a specific message type.
     *
     * Job classes must accept the message in their constructor. Closure
     * subscriptions are executed directly by the message:consume command.
     *
     * @param  class-string<Message>  $messageClass
     * @param  class-string<ShouldQueue>|Closure  $handler
     */
    public function subscribe(string $messageClass, string|Closure $handler): void
    {
        $this->subscriptions[$messageClass][] = $handler;
    }

    /**
     * Subscribe a closure to handle a specific message type.
     *
     * @param  class-string<Message>  $messageClass
     * @param  Closure  $handler
     */
    public function subscribeClosure(string $messageClass, Closure $handler): void
    {
        $this->subscribe($messageClass, $handler);
    }

    /**
     * Get all registered subscriptions.
     *
     * @return array<class-string<Message>, list<class-string<ShouldQueue>|Closure>>
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }

    /**
     * Get the queue size for a message channel.
     */
    public function getQueueSize(string $channel, ?string $connection = null): int
    {
        if ($this->monitor === null || ! method_exists($this->monitor, 'getQueueSize')) {
            return 0;
        }

        return (int) $this->monitor->getQueueSize($channel, $connection);
    }

    /**
     * Get all queues and their sizes for a connection.
     *
     * @return array<string, int>
     */
    public function getAllQueueSizes(?string $connection = null): array
    {
        if ($this->monitor === null || ! method_exists($this->monitor, 'getQueueSizes')) {
            return [];
        }

        $sizes = $this->monitor->getQueueSizes($connection);

        if (! is_array($sizes)) {
            return [];
        }

        $normalized = [];

        foreach ($sizes as $queue => $size) {
            if (is_string($queue) && is_numeric($size)) {
                $normalized[$queue] = (int) $size;
            }
        }

        return $normalized;
    }

    private function queue(): QueueFactory
    {
        if ($this->queue instanceof QueueFactory) {
            return $this->queue;
        }

        $queue = $this->queue->make(QueueFactory::class);

        if (! $queue instanceof QueueFactory) {
            throw new \LogicException('The queue factory service is not registered correctly.');
        }

        return $queue;
    }

    private function normalizeJobId(mixed $id): string
    {
        if (is_scalar($id) && (string) $id !== '') {
            return (string) $id;
        }

        return (string) Str::uuid();
    }
}
