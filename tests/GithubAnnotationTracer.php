<?php

declare(strict_types=1);

namespace LaravelModular\LaravelModular\Tests;

use PHPUnit\Event\Event;
use PHPUnit\Event\Tracer\Tracer;

/**
 * TEMPORARY sandbox debugging aid: reports failed/errored tests as GitHub
 * Actions workflow commands so the Checks API exposes them as annotations.
 * Removed before the PR is finalized.
 */
final class GithubAnnotationTracer implements Tracer
{
    private bool $seen = false;

    public function trace(Event $event): void
    {
        if (! $this->seen) {
            $this->seen = true;
            fwrite(STDERR, '::warning::GithubAnnotationTracer first event: '.$event::class.PHP_EOL);
        }

        $class = $event::class;

        if (! str_ends_with($class, 'Failed') && ! str_ends_with($class, 'Errored')) {
            return;
        }

        $details = method_exists($event, 'asString') ? $event->asString() : $class;

        if (method_exists($event, 'throwable')) {
            $details .= ' || '.$event->throwable()->asString();
        }

        $message = strtr($details, [
            '%' => '%25',
            "\r" => '%0D',
            "\n" => '%0A',
        ]);

        fwrite(STDERR, '::error::'.$message.PHP_EOL);
    }
}
