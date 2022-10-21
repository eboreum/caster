<?php

declare(strict_types=1);

namespace Eboreum\Caster\Common\DataType\Integer;

/**
 * @inheritDoc
 *
 * Contains an integer guaranteed to be >= 0.
 */
class NegativeInteger extends AbstractInteger
{
    public static function getMaximumLimit(): int
    {
        return -1;
    }
}
