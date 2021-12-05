<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

use Eboreum\Caster\Annotation\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;

abstract class ClassC implements DebugIdentifierAnnotationInterface
{
    /** @DebugIdentifier */
    public string $publicPublicPublic = 'c';

    /** @DebugIdentifier */
    protected string $publicPublicProtected = 'c';

    /** @DebugIdentifier */
    private string $publicPublicPrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    protected string $publicProtectedProtected = 'c';

    /** @DebugIdentifier */
    private string $publicProtectedPrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private string $publicPrivatePrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    protected string $protectedProtectedProtected = 'c';

    /** @DebugIdentifier */
    private string $protectedProtectedPrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private string $protectedPrivatePrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private string $privatePrivatePrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private static string $staticPrivatePrivatePrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private string $onlyInC = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    public string $onlyInBAndCPublicPublic = 'c';

    /** @DebugIdentifier */
    protected string $onlyInBAndCPublicProtected = 'c';

    /** @DebugIdentifier */
    private string $onlyInBAndCPublicPrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    protected string $onlyInBAndCProtectedProtected = 'c';

    /** @DebugIdentifier */
    private string $onlyInBAndCProtectedPrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /** @DebugIdentifier */
    private string $onlyInBAndCPrivatePrivate = 'c'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md

    /**
     * @var mixed
     */
    public $publicDoNotIncludeMe;

    /**
     * @var mixed
     */
    protected $protectedDoNotIncludeMe;

    /**
     * @var mixed
     */
    protected $privateDoNotIncludeMe;
}
