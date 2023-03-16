<?php

declare(strict_types=1);

namespace Eboreum\Caster;

use Eboreum\Caster\Exception\AssertionException;

use function is_string;
use function sprintf;

/**
 * A minimalist class for asserting values are as expected. Inspired by other libraries. However, we do not want a hard
 * dependency to these libraries from this library, so here is merely implemented the bare minimum we need.
 *
 * @see https://packagist.org/packages/webmozart/assert
 * @see https://packagist.org/packages/beberlei/assert
 */
final class Assertion
{
    /**
     * @throws AssertionException
     */
    public static function assertIsString(mixed $value, ?string $message = null): void
    {
        if (false === is_string($value)) {
            throw new AssertionException(sprintf(
                'Expects argument $value = %s to be a string, but it is not%s',
                Caster::getInternalInstance()->castTyped($value),
                ($message ? ': ' . $message : ''),
            ));
        }
    }
}
