<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;

abstract class ClassB implements DebugIdentifierAttributeInterface
{
    #[DebugIdentifier]
    private string $bar = 'b'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    private string $doNotIncludeMe = ''; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
}
