<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithASensitiveNamedArgument; // phpcs:ignore

use Attribute;
use SensitiveParameter;

#[Attribute]
class Attribute5773ba9c73ed11eeb9620242ac120002
{
    public readonly string $foo;

    public function __construct(
        #[SensitiveParameter]
        string $foo
    ) {
        $this->foo = $foo;
    }
}
