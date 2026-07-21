<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\NotificationMakeCommand as BaseNotificationMakeCommand;

final class NotificationMakeCommand extends BaseNotificationMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'Notifications';
    }
}
