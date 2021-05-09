<?php
declare(strict_types = 1);

namespace Eboreum\Caster\TestResource\Unit\Formatter\Object_\PublicVariableFormatterTest\testFormatWorksWhenObjectHasMultipleSameNamePublicVariables;

class ClassA extends ClassB
{
    public string $foo = "a";
}
