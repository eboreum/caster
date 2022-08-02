<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;

/**
 * @inheritDoc
 *
 * Handles classes, which implement the magic method `__debugInfo`.
 *
 * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
 */
class DebugInfoFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert(method_exists($object, '__debugInfo')); // Make phpstan happy

        return sprintf(
            '%s (%s)',
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            $caster->cast($object->__debugInfo()),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return method_exists($object, '__debugInfo');
    }
}
