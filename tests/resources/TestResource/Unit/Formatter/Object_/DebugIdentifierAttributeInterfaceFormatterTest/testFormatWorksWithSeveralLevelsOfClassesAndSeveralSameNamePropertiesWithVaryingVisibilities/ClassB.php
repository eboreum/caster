<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;

abstract class ClassB extends ClassC
{
    #[DebugIdentifier]
    public string $publicPublicPublic = 'b';

    #[DebugIdentifier]
    public string $publicPublicProtected = 'b';

    #[DebugIdentifier]
    public string $publicPublicPrivate = 'b';

    #[DebugIdentifier]
    protected string $publicProtectedProtected = 'b';

    #[DebugIdentifier]
    protected string $publicProtectedPrivate = 'b';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $publicPrivatePrivate = 'b';

    #[DebugIdentifier]
    protected string $protectedProtectedProtected = 'b';

    #[DebugIdentifier]
    protected string $protectedProtectedPrivate = 'b';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $protectedPrivatePrivate = 'b';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $privatePrivatePrivate = 'b';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private static string $staticPrivatePrivatePrivate = 'b';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $onlyInB = 'b';

    #[DebugIdentifier]
    public string $onlyInBAndCPublicPublic = 'b';

    #[DebugIdentifier]
    public string $onlyInBAndCPublicProtected = 'b';

    #[DebugIdentifier]
    public string $onlyInBAndCPublicPrivate = 'b';

    #[DebugIdentifier]
    protected string $onlyInBAndCProtectedProtected = 'b';

    #[DebugIdentifier]
    protected string $onlyInBAndCProtectedPrivate = 'b';

    #[DebugIdentifier]
    protected string $onlyInBAndCPrivatePrivate = 'b';

    public $publicDoNotIncludeMe; // phpcs:ignore

    protected $protectedDoNotIncludeMe; // phpcs:ignore

    protected $privateDoNotIncludeMe; // phpcs:ignore
}
