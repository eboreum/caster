<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties;

use Eboreum\Caster\Attribute\DebugIdentifier;

class ClassA extends ClassB
{
    #[DebugIdentifier]
    private string $foo = 'a'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    private string $doNotIncludeMe = ''; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
}
