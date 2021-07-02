<?php

declare(strict_types=1);

namespace Eboreum\Caster;

/**
 * Returns an array of file paths.
 *
 * Courtesy of (credits): @see https://stackoverflow.com/a/17161106
 *
 * @return array<string>
 */
function rglob(string $pattern, int $flags = 0): array
{
    $filePaths = glob($pattern, $flags);

    assert(is_array($filePaths));

    $globbedDirectoryPaths = glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT);

    assert(is_array($globbedDirectoryPaths));

    foreach ($globbedDirectoryPaths as $directoryPath) {
        $filePaths = array_merge(
            $filePaths,
            rglob($directoryPath.'/'.basename($pattern), $flags),
        );
    }

    return $filePaths;
}
