<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Directory;
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
 * Handles instances of `\Directory`.
 */
class DirectoryFormatter extends AbstractObjectFormatter
{
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof Directory); // Make phpstan happy

        return sprintf(
            '%s {$path = %s}',
            Caster::makeNormalizedClassName(new ReflectionObject($object)),
            $caster->cast($object->path),
        );
    }

    public function isHandling(object $object): bool
    {
        return boolval($object instanceof Directory);
    }
}
