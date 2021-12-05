<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

use Eboreum\Caster\Annotation\DebugIdentifier;

class ClassA extends ClassB
{
    /** @DebugIdentifier */
    public string $publicPublicPublic = 'a';

    /** @DebugIdentifier */
    public string $publicPublicProtected = 'a';

    /** @DebugIdentifier */
    public string $publicPublicPrivate = 'a';

    /** @DebugIdentifier */
    public string $publicProtectedProtected = 'a';

    /** @DebugIdentifier */
    public string $publicProtectedPrivate = 'a';

    /** @DebugIdentifier */
    public string $publicPrivatePrivate = 'a';

    /** @DebugIdentifier */
    protected string $protectedProtectedProtected = 'a';

    /** @DebugIdentifier */
    protected string $protectedProtectedPrivate = 'a';

    /** @DebugIdentifier */
    protected string $protectedPrivatePrivate = 'a';

    /** @DebugIdentifier */
    private string $privatePrivatePrivate = 'a'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private static string $staticPrivatePrivatePrivate = 'a'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private string $onlyInA = 'a'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    public $publicDoNotIncludeMe;

    protected $protectedDoNotIncludeMe;

    protected $privateDoNotIncludeMe;
}
