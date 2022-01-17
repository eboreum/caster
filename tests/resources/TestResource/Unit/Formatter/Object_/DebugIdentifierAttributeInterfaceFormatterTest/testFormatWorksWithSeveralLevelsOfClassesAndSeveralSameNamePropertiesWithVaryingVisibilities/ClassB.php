<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

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
    private string $publicPrivatePrivate = 'b'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    #[DebugIdentifier]
    protected string $protectedProtectedProtected = 'b';

    #[DebugIdentifier]
    protected string $protectedProtectedPrivate = 'b';

    #[DebugIdentifier]
    private string $protectedPrivatePrivate = 'b'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    #[DebugIdentifier]
    private string $privatePrivatePrivate = 'b'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    #[DebugIdentifier]
    private static string $staticPrivatePrivatePrivate = 'b'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    #[DebugIdentifier]
    private string $onlyInB = 'b'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

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

    public $publicDoNotIncludeMe;

    protected $protectedDoNotIncludeMe;

    protected $privateDoNotIncludeMe;
}
