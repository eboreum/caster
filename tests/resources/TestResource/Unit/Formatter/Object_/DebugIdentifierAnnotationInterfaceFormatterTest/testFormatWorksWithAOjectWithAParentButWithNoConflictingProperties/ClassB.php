<?php
declare(strict_types = 1);

namespace Eboreum\Caster\TestResource\Unit\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties;

use Eboreum\Caster\Annotation\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;

abstract class ClassB implements DebugIdentifierAnnotationInterface
{
    /**
     * @DebugIdentifier
     */
    private string $bar = "b";

    private string $doNotIncludeMe = "";
}
