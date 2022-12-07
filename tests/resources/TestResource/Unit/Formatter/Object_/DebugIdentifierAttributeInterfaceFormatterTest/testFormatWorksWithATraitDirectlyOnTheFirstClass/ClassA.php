<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithATraitDirectlyOnTheFirstClass; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;

class ClassA implements DebugIdentifierAttributeInterface
{
    use TraitA;

    #[DebugIdentifier]
    protected string $foo = 'a';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $baz = 'c';
}
