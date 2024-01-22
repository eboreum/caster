<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\Formatter;

/**
 * Used to denote that the implementing formatter class handles text wrapping, e.g. of arrays and objects.
 */
interface WrappableInterface extends FormatterInterface
{
}
