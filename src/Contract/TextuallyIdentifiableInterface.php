<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract;

use Eboreum\Caster\Contract\CasterInterface;

interface TextuallyIdentifiableInterface
{
    /**
     * Converts a class to a string respresentation.
     *
     * You MUST use the provided $caster argument and NOT e.g. Caster::getInstance(), as this provides a means for
     * avoiding endless recursive loops.
     *
     * Example:
     *
     *    sprintf(
     *        "\\%s (USER.ID = %s)",
     *        get_class($this),
     *        Caster::getInstance()->cast($this->id)
     *    )
     *
     * Which will output something like:
     *
     * \MyUserClass (USER.ID = 22)
     */
    public function toTextualIdentifier(CasterInterface $caster): string;
}
