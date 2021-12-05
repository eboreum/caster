<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;
use Eboreum\Caster\Exception\RuntimeException;

class ArrayFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    /** @var array<int, ArrayFormatterInterface> */
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
     */
    public static function getHandledClassName(): string
    {
        return ArrayFormatterInterface::class;
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
     *
     * @phpstan-ignore-next-line Suppression code 42a9f1bf; see README.md
     * @return \ArrayIterator<int, ArrayFormatterInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }
}
