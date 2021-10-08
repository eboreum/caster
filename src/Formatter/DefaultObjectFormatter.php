<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;

class DefaultObjectFormatter extends AbstractObjectFormatter
{
    /**
     * Determines whether or not the `spl_object_hash` is appended to the string in a parenthesis.
     *
     * @see https://www.php.net/manual/en/function.spl-object-hash.php
     */
    protected bool $isAppendingSplObjectHash = false;

    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        $str = Caster::makeNormalizedClassName(new \ReflectionObject($object));

        if ($this->isAppendingSplObjectHash()) {
            $str .= sprintf(
                " (%s)",
                \spl_object_hash($object),
            );
        }

        return $str;
    }

    /**
     * Must return a clone.
     */
    public function withIsAppendingSplObjectHash(bool $isAppendingSplObjectHash): ObjectFormatterInterface
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

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return true;
    }
}
