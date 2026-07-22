<?php

declare(strict_types=1);

namespace Laltu\Modular\Notifications;

use Illuminate\Notifications\Notification;

abstract class ModuleNotification extends Notification
{
    protected ?string $moduleName = null;

    public function viaModule(string $module): static
    {
        $this->moduleName = $module;
        return $this;
    }

    public function module(): ?string
    {
        return $this->moduleName;
    }

    abstract public function toArray($notifiable): array;
}
