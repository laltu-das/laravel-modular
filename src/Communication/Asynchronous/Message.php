<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

use BackedEnum;
use JsonSerializable;

/**
 * Base contract for all messages in the modular monolith.
 *
 * Messages represent asynchronous communication between modules.
 * They are serializable and dispatched to a message broker (queue).
 *
 * Unlike Events (which are synchronous by default in Laravel),
 * Messages are always processed asynchronously via queues.
 *
 * Usage:
 * ```php
 * final readonly class OrderPlaced implements Message
 * {
 *     public function __construct(
 *         public string $orderId,
 *         public int $customerId,
 *         public float $total,
 *     ) {}
 *
 *     public function channel(): string
 *     {
 *         return 'orders';
 *     }
 * }
 * ```
 */
interface Message extends JsonSerializable
{
    /**
     * Get the channel/queue name this message should be published to.
     */
    public function channel(): string;

    /**
     * Get the message priority (higher = more urgent).
     * Defaults to normal priority.
     */
    public function priority(): int
    {
        return 0;
    }

    /**
     * Get the delay in seconds before the message should be processed.
     */
    public function delay(): int
    {
        return 0;
    }

    /**
     * Get the connection name for the queue.
     */
    public function connection(): ?string
    {
        return null;
    }

    /**
     * Get the queue name (alias for channel for Laravel Queue compatibility).
     */
    public function queue(): string
    {
        return $this->channel();
    }
}