<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;

/**
 * @inheritDoc
 */
class DefaultObjectFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        $str = Caster::makeNormalizedClassName(new \ReflectionObject($object));

        if ($this->isAppendingSplObjectHash()) {
            $str .= sprintf(
                ' (%s)',
                \spl_object_hash($object),
            );
        }

        return $str;
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return true;
    }
}
