<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithATraitDirectlyOnTheFirstClass; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;

trait TraitA
{
    #[DebugIdentifier]
    protected string $bar = 'b';
}
