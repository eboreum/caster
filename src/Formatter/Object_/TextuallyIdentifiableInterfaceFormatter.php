<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;

/**
 * Handles classes, which implement `TextuallyIdentifiableInterface`.
 */
class TextuallyIdentifiableInterfaceFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        return $object->toTextualIdentifier($caster);
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($object instanceof TextuallyIdentifiableInterface);
    }
}
