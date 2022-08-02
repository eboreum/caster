<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

/**
 * @inheritDoc
 *
 * Formatter for \DateInterval.
 */
class DateIntervalFormatter extends PublicVariableFormatter
{
    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof \DateInterval);
    }
}
