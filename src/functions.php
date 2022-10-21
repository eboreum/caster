<?php

declare(strict_types=1);

namespace Eboreum\Caster\functions;

use function array_merge;
use function assert;
use function basename;
use function dirname;
use function enum_exists;
use function glob;
use function is_array;
use function is_object;

use const GLOB_NOSORT;
use const GLOB_ONLYDIR;

/**
 * Contains globally available functions.
 */

/**
 * Returns an array of file paths.
 *
 * Courtesy of (credits): @see https://stackoverflow.com/a/17161106
 *
 * @see https://www.php.net/manual/en/function.glob.php
 *
 * @param string $pattern A glob pattern, as it is used in the core PHP function `glob`.
 * @param int $flags The flags parameter as it is used in the core PHP function `glob`.
 *
 * @return array<string>
 */
function rglob(string $pattern, int $flags = 0): array
{
    $filePaths = glob($pattern, $flags);

    assert(is_array($filePaths)); // Make phpstan happy

    $globbedDirectoryPaths = glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

    assert(is_array($globbedDirectoryPaths)); // Make phpstan happy

    foreach ($globbedDirectoryPaths as $directoryPath) {
        $filePaths = array_merge(
            $filePaths,
            rglob($directoryPath . '/' . basename($pattern), $flags),
        );
    }

    return $filePaths;
}

/**
 * The missing PHP 8.1 function. Same functionality as is_object, is_array, is_null, etc. but for (actual) enums.
 */
function is_enum(mixed $value): bool
{
    return is_object($value) && enum_exists($value::class);
}
