<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

use Closure;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Queue\Monitor;
use Illuminate\Support\Facades\Queue;

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
     * @var array<class-string<Message>, array<class-string<\Illuminate\Contracts\Queue\ShouldQueue>>>
     */
    private array $subscriptions = [];

    public function __construct(
        private QueueFactory $queue,
        private ?Monitor $monitor = null,
    ) {}

    /**
     * Publish a message to the message broker (fire-and-forget).
     *
     * @param Message $message The message to publish
     * @return string The job ID
     */
    public function publish(Message $message): string
    {
        $job = new MessageJob($message);

        return $this->queue->connection($message->connection())
            ->pushOn($message->channel(), $job, $message->priority());
    }

    /**
     * Publish a message with a delay.
     *
     * @param Message $message The message to publish
     * @param int $delay Delay in seconds
     * @return string The job ID
     */
    public function publishLater(Message $message, int $delay): string
    {
        $job = new MessageJob($message);

        return $this->queue->connection($message->connection())
            ->laterOn($message->channel(), $delay, $job, $message->priority());
    }

    /**
     * Publish multiple messages in a batch.
     *
     * @param iterable<Message> $messages
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
     * Subscribe a job class to handle a specific message type.
     *
     * The job class must accept the message in its constructor.
     * This registers the subscription for the message consumer command.
     *
     * @param class-string<Message> $messageClass
     * @param class-string<\Illuminate\Contracts\Queue\ShouldQueue> $jobClass
     */
    public function subscribe(string $messageClass, string $jobClass): void
    {
        $this->subscriptions[$messageClass][] = $jobClass;
    }

    /**
     * Subscribe a closure to handle a specific message type.
     *
     * Note: Closure subscriptions only work with the message:consume command.
     *
     * @param class-string<Message> $messageClass
     * @param Closure(Message): void $handler
     */
    public function subscribeClosure(string $messageClass, Closure $handler): void
    {
        $this->subscriptions[$messageClass][] = $handler;
    }

    /**
     * Get all registered subscriptions.
     *
     * @return array<class-string<Message>, array<class-string<\Illuminate\Contracts\Queue\ShouldQueue>|Closure>>
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
        if ($this->monitor === null) {
            return 0;
        }

        return $this->monitor->getQueueSize($channel, $connection);
    }

    /**
     * Get all queues and their sizes for a connection.
     *
     * @return array<string, int>
     */
    public function getAllQueueSizes(?string $connection = null): array
    {
        if ($this->monitor === null) {
            return [];
        }

        return $this->monitor->getQueueSizes($connection);
    }
}

/**
 * Internal job that wraps a Message for queue processing.
 * The message consumer command will extract the message and dispatch the appropriate handler jobs.
 */
final class MessageJob implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Queue\SerializesModels;

    public function __construct(
        public readonly Message $message,
    ) {}

    public function handle(): void
    {
        // This job is a marker - the actual handling is done by the message consumer
        // which reads this job from the queue and dispatches the appropriate handlers
    }
}