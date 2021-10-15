<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;

/**
 * Formatter for \DateTimeZone.
 */
class DateTimeZoneFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null;
        }

        assert($object instanceof \DateTimeZone);

        return sprintf(
            '%s (name: %s)',
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            $caster->cast($object->getName()),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof \DateTimeZone);
    }
}
