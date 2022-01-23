<?php

declare(strict_types=1);

namespace Eboreum\Caster\Abstraction\Formatter;

/**
 * {@inheritDoc}
 */
abstract class AbstractObjectTypeFormatter extends AbstractDimensionalFormatter
{
        /**
     * Determines whether or not the `spl_object_hash` is appended to the string in a parenthesis.
     *
     * @see https://www.php.net/manual/en/function.spl-object-hash.php
     */
    protected bool $isAppendingSplObjectHash = false;


    /**
     * Must return a clone.
     *
     * @return static
     */
    public function withIsAppendingSplObjectHash(bool $isAppendingSplObjectHash): self
    {
        $clone = clone $this;
        $clone->isAppendingSplObjectHash = $isAppendingSplObjectHash;

        return $clone;
    }

    /**
     * Returns whether or not the `spl_object_hash` is appended to the string in a parenthesis.
     *
     * @see https://www.php.net/manual/en/function.spl-object-hash.php
     */
    public function isAppendingSplObjectHash(): bool
    {
        return $this->isAppendingSplObjectHash;
    }
}
