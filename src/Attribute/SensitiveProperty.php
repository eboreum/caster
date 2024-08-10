<?php

declare(strict_types=1);

namespace Eboreum\Caster\Attribute;

use Attribute;

/**
 * An attribut with functionality similar to that of \SensitiveParameter, but for class properties instead. When
 * specified on a class property, the contents of said property must never be shown when being formatted by Caster. It
 * must not even display its type (parameter type hint). For this library specifically, the text "** REDACTED **" will
 * be displayed instead. However, this text may be changed by overriding "Caster->getSensitiveMessage()".
 *
 * @see https://www.php.net/manual/en/class.sensitiveparameter.php
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SensitiveProperty
{
}
