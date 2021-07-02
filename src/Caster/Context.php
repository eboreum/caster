<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Caster;

use Eboreum\Caster\Contract\Caster\ContextInterface;

/**
 * {@inheritDoc}
 */
class Context implements ContextInterface
{
    /**
     * @var array<string, object>
     */
    protected array $visitedObjectStack = [];

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->visitedObjectStack);
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedVisitedObject(object $object): ContextInterface
    {
        $hash = spl_object_hash($object);
        $clone = clone $this;

        if (false === array_key_exists($hash, $clone->visitedObjectStack)) {
            $clone->visitedObjectStack[$hash] = $object;
        }

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function hasVisitedObject(object $object): bool
    {
        $hash = spl_object_hash($object);

        return array_key_exists($hash, $this->visitedObjectStack);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->visitedObjectStack);
    }
}
