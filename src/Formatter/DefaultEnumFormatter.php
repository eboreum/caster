<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Functions;
use ReflectionEnum;
use UnitEnum;

use function assert;
use function spl_object_hash;
use function sprintf;

class DefaultEnumFormatter extends AbstractObjectFormatter
{
    public function format(CasterInterface $caster, object $enum): ?string
    {
        if (false === $this->isHandling($enum)) {
            return null;
        }

        assert($enum instanceof UnitEnum);

        $name = $enum->name;
        $str = sprintf(
            '%s {$name = %s}',
            Caster::makeNormalizedClassName(new ReflectionEnum($enum)),
            $caster->cast($name),
        );

        if ($this->isAppendingSplObjectHash()) {
            $str .= sprintf(
                ' (%s)',
                spl_object_hash($enum),
            );
        }

        return $str;
    }

    public function isHandling(object $enum): bool
    {
        return Functions::isEnum($enum);
    }
}
