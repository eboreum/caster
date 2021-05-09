<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;

class ArrayFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    /**
     * @var array<int, ArrayFormatterInterface>
     */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function __construct(ArrayFormatterInterface ...$elements)
    {
        parent::__construct(...$elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, ArrayFormatterInterface>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledClassName(): string
    {
        return ArrayFormatterInterface::class;
    }
}
