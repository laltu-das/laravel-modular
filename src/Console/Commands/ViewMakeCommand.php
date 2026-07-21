<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Console\Commands;

use Illuminate\Foundation\Console\ViewMakeCommand as BaseViewMakeCommand;

final class ViewMakeCommand extends BaseViewMakeCommand
{
    use ModuleAwareGenerator;

    protected function moduleDirectory(): string
    {
        return 'resources/views';
    }

    /**
     * Views are Blade templates, not PHP classes: honor the base command's
     * --extension option (blade.php by default) instead of the generic .php.
     */
    protected function moduleFileExtension(): string
    {
        $extension = $this->option('extension');

        return is_string($extension) && $extension !== '' ? $extension : 'blade.php';
    }
}
