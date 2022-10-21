<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use DateInterval;

use function boolval;

/**
 * @inheritDoc
 *
 * Formatter for \DateInterval.
 */
class DateIntervalFormatter extends PublicVariableFormatter
{
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof DateInterval);
    }
}
