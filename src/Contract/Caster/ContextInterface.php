<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\Caster;

use Countable;
use Eboreum\Caster\Contract\ImmutableObjectInterface;

/**
 * @inheritDoc
 *
 * A context used to determine, if an object has already been visited. This check is vital in order to avoid endless
 * recursion.
 */
interface ContextInterface extends ImmutableObjectInterface, Countable
{
    /**
     * Add a visited object to the stack.
     */
    public function withAddedVisitedObject(object $object): ContextInterface;

    /**
     * Must return `true` when an object exists in the current stack.
     * Otherwise, must return `false`.
     */
    public function hasVisitedObject(object $object): bool;

    /**
     * Whther the visited object stack in the implementing class is empty or not. Faster than: 0 === count($context)
     */
    public function isEmpty(): bool;
}
