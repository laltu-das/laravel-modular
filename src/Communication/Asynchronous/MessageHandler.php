<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

/**
 * Interface for message handlers (consumers).
 *
 * Modules implement this to handle specific message types.
 * Handlers are registered via MessageBus::subscribe() or by
 * creating a Job that implements ShouldQueue.
 *
 * Usage:
 * ```php
 * final class OrderPlacedHandler implements MessageHandler
 * {
 *     public function handles(): string
 *     {
 *         return OrderPlaced::class;
 *     }
 *
 *     public function handle(OrderPlaced $message): void
 *     {
 *         // Process the message
 *     }
 * }
 * ```
 */
interface MessageHandler
{
    /**
     * Get the message class this handler processes.
     */
    public function handles(): string;

    /**
     * Handle the message.
     */
    public function handle(Message $message): void;
}