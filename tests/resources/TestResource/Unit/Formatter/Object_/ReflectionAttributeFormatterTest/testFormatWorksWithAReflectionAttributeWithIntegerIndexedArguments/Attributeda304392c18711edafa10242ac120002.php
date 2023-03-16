<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWithIntegerIndexedArguments;

use Attribute;

#[Attribute]
class Attributeda304392c18711edafa10242ac120002
{
    public readonly string $foo;

    public readonly string $bar;

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
