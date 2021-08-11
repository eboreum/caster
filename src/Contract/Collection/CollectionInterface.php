<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\Collection;

use Eboreum\Caster\Contract\ImmutableObjectInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Exception\CollectionException;

/**
 * {@inheritDoc}
 *
 * @extends \IteratorAggregate<int, ElementInterface>
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
     * @return \ArrayIterator<int, ElementInterface>
     */
    public function getIterator(): \ArrayIterator;

    /**
     * Must return whether the collection is empty or not.
     */
    public function isEmpty(): bool;


    /**
     * Must return true when the $element argument is accepted by the implementing class.
     * Otherwise, must return false.
     *
     * @param mixed $element
     */
    public static function isElementAccepted($element): bool;
}
