<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionObject;

use function spl_object_hash;
use function sprintf;

class DefaultObjectFormatter extends AbstractObjectFormatter
{
    public function format(CasterInterface $caster, object $object): ?string
    {
        $str = Caster::makeNormalizedClassName(new ReflectionObject($object));

        if ($this->isAppendingSplObjectHash()) {
            $str .= sprintf(
                ' (%s)',
                spl_object_hash($object),
            );
        }

        return $str;
    }

    public function isHandling(object $object): bool
    {
        return true;
    }
}
