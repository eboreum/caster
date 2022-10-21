<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\EncryptedString;

/**
 * {@inheritDoc}
 *
 * @template T of EncryptedString
 * @extends AbstractObjectCollection<T>
 */
class EncryptedStringCollection extends AbstractObjectCollection
{
    public static function getHandledClassName(): string
    {
        return EncryptedString::class;
    }
}
