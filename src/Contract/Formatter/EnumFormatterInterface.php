<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\Formatter;

use Eboreum\Caster\Contract\CasterInterface;

/**
 * @inheritDoc
 */
interface EnumFormatterInterface extends DimensionalFormatterInterface
{
    /**
     * Apply logic, which converts an enum into a human readable string. Employed methods may include custom
     * conversion, `json_encode`, `serialize`, and more.
     *
     * If a non-enum is provided, this method must return `null` or alternatively throw an
     * `\InvalidArgumentException`, containing a suitable message.
     *
     * When `null` is returned, the next custom enum formatter is called. If all custom enum formatters return
     * `null`, the default enum-to-string logic (`DefaultEnumFormatter`) is applied.
     *
     * The implementing method MUST check for and handle cyclic recursion.
     */
    public function format(CasterInterface $caster, object $enum): ?string;

    /**
     * Whether or not the object is an enum and is qualified to be handled by the implementing formatter.
     */
    public function isHandling(object $enum): bool;
}
