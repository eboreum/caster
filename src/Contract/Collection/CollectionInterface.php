<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\Collection;

use ArrayIterator;
use Countable;
use Eboreum\Caster\Contract\ImmutableObjectInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use IteratorAggregate;

/**
 * {@inheritDoc}
 *
 * @template T of mixed
 * @extends IteratorAggregate<(int|string), T>
 */
interface CollectionInterface extends
    ImmutableObjectInterface,
    TextuallyIdentifiableInterface,
    Countable,
    IteratorAggregate
{
    /**
     * Must return true when the $element argument is accepted by the implementing class.
     * Otherwise, must return false.
     */
    public static function isElementAccepted(mixed $element): bool;

    /**
     * @return array<T>
     */
    public function toArray(): array;

    /**
     * {@inheritDoc}
     *
     * @return ArrayIterator<(int|string), T>
     */
    public function getIterator(): ArrayIterator;

    /**
     * Must return whether the collection is empty or not.
     */
    public function isEmpty(): bool;
}
