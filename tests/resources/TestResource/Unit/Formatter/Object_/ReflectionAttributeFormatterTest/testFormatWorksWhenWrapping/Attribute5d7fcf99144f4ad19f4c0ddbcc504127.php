<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWhenWrapping; // phpcs:ignore

use Attribute;

#[Attribute]
class Attribute5d7fcf99144f4ad19f4c0ddbcc504127
{
    public readonly string $foo;

    /** @var array<int> */
    public readonly array $bar;

    /**
     * @param array<int> $bar
     */
    public function __construct(string $foo, array $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
