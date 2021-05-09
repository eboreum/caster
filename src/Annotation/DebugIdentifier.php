<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Annotation;

/**
 * Provide this annotation on properties to allow the \Eboreum\Caster\Caster class, when an appropriate formatter like
 * \Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatter is specified, to extract and display
 * the contained value of the respective property.
 *
 * You MUST implement \Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface on the classes utilizing this property
 * for the formatter to work. Otherwise, it will not look for this annotation.
 *
 * Requires package: doctrine/annotations
 *
 * As the underlying logic for this annotation utilizes the Reflection API
 *  (https://www.php.net/manual/en/book.reflection.php), which is slow, it is recommended mainly be use the displaying
 * of this annotation's property value in failure scenarios, e.g. as part of building an exception message.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class DebugIdentifier
{
}
