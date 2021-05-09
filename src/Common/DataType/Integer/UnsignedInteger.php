<?php

declare(strict_types=1);

namespace Eboreum\Caster\Common\DataType\Integer;

/**
 * Contains an integer guaranteed to be >= 0.
 */
class UnsignedInteger extends AbstractInteger
{
    /**
     * {@inheritDoc}
     */
    public static function getMinimumLimit(): int
    {
        return 0;
    }
}
