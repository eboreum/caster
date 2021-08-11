<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;
use Eboreum\Caster\Exception\RuntimeException;

class ObjectFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    /**
     * @var array<int, ObjectFormatterInterface>
     */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function __construct(ObjectFormatterInterface ...$elements)
    {
        parent::__construct(...$elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, ObjectFormatterInterface>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     *
     * @return \ArrayIterator<int, ObjectFormatterInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledClassName(): string
    {
        return ObjectFormatterInterface::class;
    }
}
