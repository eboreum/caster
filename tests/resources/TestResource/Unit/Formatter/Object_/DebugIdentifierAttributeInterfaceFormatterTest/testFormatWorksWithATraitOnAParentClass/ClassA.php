<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithATraitOnAParentClass; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;

class ClassA extends ClassB
{
    #[DebugIdentifier]
    protected string $foo = 'a';
}
