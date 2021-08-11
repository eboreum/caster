<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;

class DefaultObjectFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        return Caster::makeNormalizedClassName(new \ReflectionObject($object));
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return true;
    }
}
