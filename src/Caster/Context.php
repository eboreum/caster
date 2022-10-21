<?php

declare(strict_types=1);

namespace Eboreum\Caster\Caster;

use Eboreum\Caster\Contract\Caster\ContextInterface;

use function array_key_exists;
use function count;
use function spl_object_hash;

class Context implements ContextInterface
{
    /** @var array<string, object> */
    protected array $visitedObjectStack = [];

    public function count(): int
    {
        return count($this->visitedObjectStack);
    }

    public function withAddedVisitedObject(object $object): ContextInterface
    {
        $hash = spl_object_hash($object);
        $clone = clone $this;

        if (false === array_key_exists($hash, $clone->visitedObjectStack)) {
            $clone->visitedObjectStack[$hash] = $object;
        }

        return $clone;
    }

    public function hasVisitedObject(object $object): bool
    {
        $hash = spl_object_hash($object);

        return array_key_exists($hash, $this->visitedObjectStack);
    }

    public function isEmpty(): bool
    {
        return !$this->visitedObjectStack;
    }
}
