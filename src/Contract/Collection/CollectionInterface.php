<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Contract\Collection;

use Eboreum\Caster\Contract\ImmutableObjectInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Exception\CollectionException;

/**
 * {@inheritDoc}
 */
interface CollectionInterface
    extends
        ImmutableObjectInterface,
        TextuallyIdentifiableInterface,
        \Countable,
        \IteratorAggregate
{
    /**
     * @return array<int, ElementInterface>
     */
    public function toArray(): array;

    /**
     * {@inheritDoc}
     *
     * @return \Traversable<int|string, ElementInterface>
     */
    public function getIterator(): \ArrayIterator;

    /**
     * Must return whether the collection is empty or not.
     */
    public function isEmpty(): bool;


    /**
     * Must return true when the $element argument is accepted by the implementing class.
     * Otherwise, must return false.
     */
    public static function isElementAccepted(ElementInterface $element): bool;

    /**
     * Must validate whether the provided argument $element is accepted by the implementing collection class.
     * Must return an \InvalidArgumentException when the $element argument is NOT valid.
     * Otherwise, must return null.
     */
    public static function validateIsElementAccepted(ElementInterface $element): ?\InvalidArgumentException;
}
