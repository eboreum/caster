<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionObject;
use SplFileInfo;

use function assert;
use function boolval;
use function is_string;
use function sprintf;
use function strval;

/**
 * @inheritDoc
 *
 * Handles instances of `\SplFileInfo`.
 */
class SplFileInfoFormatter extends AbstractObjectFormatter
{
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof SplFileInfo); // Make phpstan happy
        assert(is_string($object->getRealPath())); // Make phpstan happy

        return sprintf(
            '%s (%s)',
            Caster::makeNormalizedClassName(new ReflectionObject($object)),
            strval($caster->getDefaultStringFormatter()->format($caster, $object->getRealPath())),
        );
    }

    public function isHandling(object $object): bool
    {
        return boolval($object instanceof SplFileInfo);
    }
}
