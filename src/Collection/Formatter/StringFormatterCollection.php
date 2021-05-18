<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\StringFormatterInterface;
use Eboreum\Caster\Exception\RuntimeException;

class StringFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    /**
     * @var array<int, StringFormatterInterface>
     */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function __construct(StringFormatterInterface ...$elements)
    {
        parent::__construct(...$elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, StringFormatterInterface>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     *
     * @return \ArrayIterator<int, StringFormatterInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return parent::getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledClassName(): string
    {
        return StringFormatterInterface::class;
    }
}
