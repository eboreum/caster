<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;

/**
 * Handles instances of `\SplFileInfo`.
 */
class SplFileInfoFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof \SplFileInfo);
        assert(is_string($object->getRealPath()));

        return sprintf(
            "%s (%s)",
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            strval($caster->getDefaultStringFormatter()->format($caster, $object->getRealPath())),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof \SplFileInfo);
    }
}
