<?php

declare(strict_types=1);

namespace Laltu\Modular\Console\Commands;

use Illuminate\Console\Command;
use Laltu\Modular\Broadcasting\ModuleBroadcast;

final class ModuleBroadcastEvent extends Command
{
    protected $signature = 'module:broadcast {module? : Module name} {--channel=} {--message=}';
    protected $description = 'Broadcast a module-level event';

    public function handle(): int
    {
        $module = $this->argument('module');
        $channel = $this->option('channel');
        $message = $this->option('message') ?? 'Broadcast from module';

        if (! $module) {
            $this->error('Module name is required.');
            return 1;
        }

        $broadcast = new ModuleBroadcast($module);
        $event = new \stdClass();
        $event->message = $message;
        $event->channel = $channel ?? ('modular.' . $module);

        $broadcast->broadcast($event, $event->channel);

        $this->info('Broadcasted event on ['.$event->channel.'] for module ['.$module.'].');
        return 0;
    }
}
