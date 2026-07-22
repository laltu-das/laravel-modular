<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Internal job that wraps a Message for queue processing.
 *
 * The message consumer command extracts the message and dispatches the
 * registered handlers for the message class.
 */
final class MessageJob implements ShouldQueue
{
    use SerializesModels;

    public function __construct(
        public readonly Message $message,
    ) {}

    public function handle(): void
    {
        // This job is a marker. Registered consumers perform the actual work.
    }
}
