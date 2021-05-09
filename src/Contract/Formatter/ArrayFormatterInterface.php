<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Contract\Formatter;

use Eboreum\Caster\Contract\CasterInterface;

interface ArrayFormatterInterface extends DimensionalFormatterInterface
{
    /**
     * Apply logic, which converts an array's contents into a human readable string. Employed methods may include custom
     * conversion, `json_encode`, `serialize`, and more.
     *
     * When `null` is returned, the next custom array formatter is called. If all custom array formatters return `null`,
     * the default array-to-string logic (`DefaultArrayFormatter`) is applied.
     */
    public function format(CasterInterface $caster, array $array): ?string;

    /**
     * Whether or not the $array argument is qualified to be handled by the formatter class implementing this
     * interface.
     */
    public function isHandling(array $array): bool;
}
