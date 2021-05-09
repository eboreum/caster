<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;

/**
 * Formatter for \DatePeriod.
 */
class DatePeriodFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null;
        }

        return sprintf(
            "%s (start: %s, end: %s, recurrences: %s, interval: %s)",
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            $caster->cast($object->getStartDate()),
            $caster->cast($object->getEndDate()),
            $caster->cast($object->getRecurrences()),
            $caster->cast($object->getDateInterval()),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof \DatePeriod);
    }
}
