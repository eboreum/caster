<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Contract\Collection\Formatter\FormatterCollectionInterface;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;

class ResourceFormatterCollection extends AbstractObjectCollection implements FormatterCollectionInterface
{
    /**
     * @var array<int, ResourceFormatterInterface>
     */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function __construct(ResourceFormatterInterface ...$elements)
    {
        parent::__construct(...$elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, ResourceFormatterInterface>
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
        return ResourceFormatterInterface::class;
    }
}
