<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;

/**
 * {@inheritDoc}
 *
 * @template T of ResourceFormatterInterface
 * @extends AbstractObjectCollection<T>
 * @implements FormatterCollectionInterface<T>
 */
class ResourceFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    public static function getHandledClassName(): string
    {
        return ResourceFormatterInterface::class;
    }
}
