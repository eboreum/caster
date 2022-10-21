<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionEnum;

use function Eboreum\Caster\functions\is_enum;
use function spl_object_hash;
use function sprintf;

class DefaultEnumFormatter extends AbstractObjectFormatter
{
    public function format(CasterInterface $caster, object $enum): ?string
    {
        if (false === is_enum($enum)) {
            return null;
        }

        $name = $enum->name; // @phpstan-ignore-line PHPStan doesn't understand this is guaranteed an enum
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
        return is_enum($enum);
    }
}
