<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\StringFormatterInterface;

/**
 * {@inheritDoc}
 *
 * @template T of StringFormatterInterface
 * @extends AbstractObjectCollection<T>
 * @implements FormatterCollectionInterface<T>
 */
class StringFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    public static function getHandledClassName(): string
    {
        return StringFormatterInterface::class;
    }
}
