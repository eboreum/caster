<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;

class ClassA extends ClassB
{
    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private static string $staticPrivatePrivatePrivate = 'a';

    #[DebugIdentifier]
    public string $publicPublicPublic = 'a';

    #[DebugIdentifier]
    public string $publicPublicProtected = 'a';

    #[DebugIdentifier]
    public string $publicPublicPrivate = 'a';

    #[DebugIdentifier]
    public string $publicProtectedProtected = 'a';

    #[DebugIdentifier]
    public string $publicProtectedPrivate = 'a';

    #[DebugIdentifier]
    public string $publicPrivatePrivate = 'a';

    #[DebugIdentifier]
    protected string $protectedProtectedProtected = 'a';

    #[DebugIdentifier]
    protected string $protectedProtectedPrivate = 'a';

    #[DebugIdentifier]
    protected string $protectedPrivatePrivate = 'a';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $privatePrivatePrivate = 'a';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $onlyInA = 'a';

    public $publicDoNotIncludeMe; // phpcs:ignore

    protected $protectedDoNotIncludeMe; // phpcs:ignore

    protected $privateDoNotIncludeMe; // phpcs:ignore
}
