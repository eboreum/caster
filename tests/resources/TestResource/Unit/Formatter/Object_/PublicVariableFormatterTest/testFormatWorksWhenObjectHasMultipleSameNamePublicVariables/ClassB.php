<?php
declare(strict_types = 1);

namespace Eboreum\Caster\TestResource\Unit\Formatter\Object_\PublicVariableFormatterTest\testFormatWorksWhenObjectHasMultipleSameNamePublicVariables;

abstract class ClassB extends ClassC
{
    public string $foo = "b";
}
