<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\EnumFormatterInterface;
use Eboreum\Caster\Exception\RuntimeException;

class EnumFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    /** @var array<int, EnumFormatterInterface> */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function __construct(EnumFormatterInterface ...$elements)
    {
        parent::__construct(...$elements);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledClassName(): string
    {
        return EnumFormatterInterface::class;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, EnumFormatterInterface>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-ignore-next-line Suppression code 42a9f1bf; see README.md
     * @return \ArrayIterator<int, EnumFormatterInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }
}
