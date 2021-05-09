<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;

/**
 * Handles instances of `\Directory`.
 */
class DirectoryFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        return sprintf(
            "%s {\$path = %s}",
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            $caster->cast($object->path),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof \Directory);
    }
}
