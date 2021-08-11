<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract;

/**
 * Denotes that the implementing class must be immutable.
 *
 * The implementing class should utilize `with*` methods, and these `with*` methods must return clones of the the
 * current object.
 */
interface ImmutableObjectInterface
{

}
