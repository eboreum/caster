<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\EnumFormatterInterface;

/**
 * @inheritDoc
 *
 * @template T of EnumFormatterInterface
 * @extends AbstractObjectCollection<T>
 * @implements FormatterCollectionInterface<T>
 */
class EnumFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    public static function getHandledClassName(): string
    {
        return EnumFormatterInterface::class;
    }
}
