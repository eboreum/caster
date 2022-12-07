<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithATraitOnAParentClass; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;

abstract class ClassB implements DebugIdentifierAttributeInterface
{
    use TraitB;

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $baz = 'c';
}
