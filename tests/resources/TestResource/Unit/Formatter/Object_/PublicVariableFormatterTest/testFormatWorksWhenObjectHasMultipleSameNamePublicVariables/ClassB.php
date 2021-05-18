<?php
declare(strict_types = 1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\PublicVariableFormatterTest\testFormatWorksWhenObjectHasMultipleSameNamePublicVariables;

abstract class ClassB extends ClassC
{
    public string $foo = "b";
}
