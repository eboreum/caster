<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWitNamedArguments;

use Attribute;

#[Attribute]
class Attributef982a9e0c18911edafa10242ac120002
{
    public readonly string $foo;

    public readonly string $bar;

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
