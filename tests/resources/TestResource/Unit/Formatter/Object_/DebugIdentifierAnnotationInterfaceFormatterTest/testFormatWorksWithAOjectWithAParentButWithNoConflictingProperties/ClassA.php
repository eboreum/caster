<?php
declare(strict_types = 1);

namespace Eboreum\Caster\TestResource\Unit\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties;

use Eboreum\Caster\Annotation\DebugIdentifier;

class ClassA extends ClassB
{
    /**
     * @DebugIdentifier
     */
    private string $foo = "a";

    private string $doNotIncludeMe = "";
}
