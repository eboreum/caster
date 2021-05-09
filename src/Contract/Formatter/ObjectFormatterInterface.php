<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Contract\Formatter;

use Eboreum\Caster\Contract\CasterInterface;

interface ObjectFormatterInterface extends DimensionalFormatterInterface
{
    /**
     * Apply logic, which converts an object into a human readable string. Employed methods may include custom
     * conversion, `json_encode`, `serialize`, and more.
     *
     * If a non-object is provided, this method must return `null` or alternatively throw an
     * `\InvalidArgumentException`, containing a suitable message.
     *
     * When `null` is returned, the next custom object formatter is called. If all custom object formatters return
     * `null`, the default object-to-string logic (`DefaultObjectFormatter`) is applied.
     *
     * The implementing method MUST check for and handle cyclic recursion.
     */
    public function format(CasterInterface $caster, object $object): ?string;

    /**
     * Whether or not the object is qualified to be handled by the implementing formatter.
     */
    public function isHandling(object $object): bool;
}
