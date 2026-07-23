<?php

declare(strict_types=1);

namespace Laltu\Modular\Communication\Asynchronous;

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
