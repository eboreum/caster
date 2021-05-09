<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Contract\Collection;

/**
 * {@inheritDoc}
 *
 * The implementing class must contanin objects of a given instance, exclusively.
 */
interface ObjectCollectionInterface extends CollectionInterface
{
    /**
     * Must return the name of the class (with full namespace) allowed inside the implementing collection class.
     */
    public static function getHandledClassName(): string;
}
