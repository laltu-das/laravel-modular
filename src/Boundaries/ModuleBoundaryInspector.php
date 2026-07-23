<?php

declare(strict_types=1);

namespace Laltu\Modular\Boundaries;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Laltu\Modular\Support\Module;

/**
 * Spring Modulith-style boundary verification. A module's public API is made
 * of its top-level public directories (Contracts, Events, Enums by default);
 * every other top-level directory is internal. A module that references
 * another module's internal classes is reported as a violation.
 */
final readonly class ModuleBoundaryInspector
{
    public function __construct(private Filesystem $files)
    {
        //
    }

    /**
     * @param array<int, Module> $modules
     * @param list<string> $publicDirectories
     * @return list<array{module: string, file: string, referenced: string}>
     * @throws FileNotFoundException
     */
    public function inspect(array $modules, string $namespace, array $publicDirectories): array
    {
        $root = trim($namespace, '\\');
        $violations = [];

        foreach ($modules as $module) {
            foreach ($this->files->allFiles($module->path()) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                foreach ($this->referencesTo($this->files->get($file->getPathname()), $root) as [$targetModule, $topLevel, $reference]) {
                    if (strcasecmp($targetModule, $module->name) === 0 || in_array($topLevel, $publicDirectories, true)) {
                        continue;
                    }

                    $violations[] = [
                        'module' => $module->name,
                        'file' => $file->getRelativePathname(),
                        'referenced' => $reference,
                    ];
                }
            }
        }

        return $violations;
    }

    /**
     * Extract [module, top-level directory, reference] triples for every
     * occurrence of the module root namespace inside the given source.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function referencesTo(string $contents, string $root): array
    {
        $separator = preg_quote('\\', '/');
        $pattern = '/'.$separator.'?'.preg_quote($root, '/').$separator.'([A-Za-z0-9_]+)'.$separator.'([A-Za-z0-9_]+)/';
        $matches = [];

        if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER) === false) {
            return [];
        }

        $references = [];

        foreach ($matches as $match) {
            $references[] = [$match[1], $match[2], ltrim($match[0], '\\')];
        }

        return $references;
    }
}
