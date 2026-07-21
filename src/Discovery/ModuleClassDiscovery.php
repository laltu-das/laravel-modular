<?php

declare(strict_types=1);

namespace Laltu\LaravelModular\Discovery;

use Illuminate\Filesystem\Filesystem;
use Laltu\LaravelModular\Support\Module;
use ReflectionClass;

/**
 * Convention-based class discovery inside a module, similar to Spring's
 * classpath scanning: any concrete class below a conventional directory is a
 * candidate for auto-registration.
 */
final readonly class ModuleClassDiscovery
{
    public function __construct(private Filesystem $files)
    {
        //
    }

    /**
     * Get the instantiable classes declared below one of the module's directories.
     *
     * @return list<class-string>
     */
    public function concreteIn(Module $module, string $directory): array
    {
        $concrete = [];

        foreach ($this->classesIn($module, $directory) as $class) {
            if ((new ReflectionClass($class))->isInstantiable()) {
                $concrete[] = $class;
            }
        }

        return $concrete;
    }

    /**
     * Get the loadable classes declared below one of the module's directories.
     *
     * @return list<class-string>
     */
    public function classesIn(Module $module, string $directory): array
    {
        $path = $module->path($directory);

        if (! $this->files->isDirectory($path)) {
            return [];
        }

        $classes = [];

        foreach ($this->files->allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
            $candidate = $module->class($directory.'/'.substr($relative, 0, -4));

            if (class_exists($candidate)) {
                $classes[] = $candidate;
            }
        }

        sort($classes);

        return $classes;
    }
}
