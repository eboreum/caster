<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties;

use Eboreum\Caster\Annotation\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;

abstract class ClassB implements DebugIdentifierAnnotationInterface
{
    /**
     * @DebugIdentifier
     */
    private string $bar = 'b';

    private string $doNotIncludeMe = '';
}
