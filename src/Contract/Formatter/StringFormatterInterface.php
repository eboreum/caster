<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Contract\Formatter;

use Eboreum\Caster\Contract\CasterInterface;

interface StringFormatterInterface extends FormatterInterface
{
    /**
     * {@inheritDoc}
     *
     * When `null` is returned, the next custom formatter is called. If all custom formatters return `null`, the
     * default string formatter logic (`DefaultResourceFormatter`) is applied.
     *
     * If the argument $string is not accepted by `isHandling`, this method must return `null`.
     */
    public function format(CasterInterface $caster, string $string): ?string;

    /**
     * Whether or not the $string argument is qualified to be handled by the formatter class implementing this
     * interface.
     *
     * The String object is utilized here to ensure reliable detection of endless recursive loops.
     */
    public function isHandling(string $string): bool;
}
