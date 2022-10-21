<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use DateTimeZone;
use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionObject;

use function assert;
use function boolval;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formatter for \DateTimeZone.
 */
class DateTimeZoneFormatter extends AbstractObjectFormatter
{
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null;
        }

        assert($object instanceof DateTimeZone); // Make phpstan happy

        return sprintf(
            '%s (name: %s)',
            Caster::makeNormalizedClassName(new ReflectionObject($object)),
            $caster->cast($object->getName()),
        );
    }

    public function isHandling(object $object): bool
    {
        return boolval($object instanceof DateTimeZone);
    }
}
