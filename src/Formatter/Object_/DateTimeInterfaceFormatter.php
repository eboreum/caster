<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;

/**
 * Prints class name and ISO 8601 datetime in parenthesis. Example: \DateTimeImmutable ("2019-01-01T00:00:00+00:00")
 */
class DateTimeInterfaceFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null;
        }

        assert($object instanceof \DateTimeInterface); // Make phpstan happy

        return sprintf(
            '%s (%s)',
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            $caster->withIsPrependingType(false)->cast(strval($object->format('c'))),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof \DateTimeInterface);
    }
}
