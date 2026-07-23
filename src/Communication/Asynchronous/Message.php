<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

use JsonSerializable;

interface Message extends JsonSerializable
{
    /**
     * Get the channel/queue name this message should be published to.
     */
    public function channel(): string;

    /**
     * Get the message priority (higher = more urgent).
     */
    public function priority(): int;

    /**
     * Get the delay in seconds before the message should be processed.
     */
    public function delay(): int;

    /**
     * Get the connection name for the queue.
     */
    public function connection(): ?string;

    /**
     * Get the queue name (alias for channel for Laravel Queue compatibility).
     */
    public function queue(): string;
}
