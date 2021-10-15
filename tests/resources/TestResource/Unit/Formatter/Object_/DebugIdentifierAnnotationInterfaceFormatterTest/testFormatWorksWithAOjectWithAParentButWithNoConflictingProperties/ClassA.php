<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties;

use Eboreum\Caster\Annotation\DebugIdentifier;

class ClassA extends ClassB
{
    /**
     * @DebugIdentifier
     */
    private string $foo = 'a';

    private string $doNotIncludeMe = '';
}
