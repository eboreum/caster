<?php

declare(strict_types=1);

namespace Eboreum\Caster\Common\DataType\Integer;

/**
 * @inheritDoc
 *
 * Contains an integer guaranteed to be >= 0.
 */
class PositiveInteger extends AbstractInteger
{
    /**
     * {@inheritDoc}
     */
    public static function getMinimumLimit(): int
    {
        return 1;
    }
}
