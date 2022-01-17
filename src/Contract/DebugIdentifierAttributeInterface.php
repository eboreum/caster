<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract;

/**
 * Classes, which implement this interface, should provide the attribute ...
 *
 *     \Eboreum\Caster\Attribute\DebugIdentifier
 *
 * ... on properties, you wish to expose. Be wary! You may leak sensitive information. However, masking of supplied
 * strings will be performed.
 *
 * As the logic behind this interface will invoke the usage of the Reflection API
 * (https://www.php.net/manual/en/book.reflection.php), which is slow, this interface and associated properties should
 * mainly be used in failure scenarios, e.g. as part of building an exception message.
 */
interface DebugIdentifierAttributeInterface
{
}
