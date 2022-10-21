<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;

/**
 * {@inheritDoc}
 *
 * @template T of ObjectFormatterInterface
 * @extends AbstractObjectCollection<T>
 * @implements FormatterCollectionInterface<T>
 */
class ObjectFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    public static function getHandledClassName(): string
    {
        return ObjectFormatterInterface::class;
    }
}
