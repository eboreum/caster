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
    private string $publicPublicPrivate = 'c';

    /** @DebugIdentifier */
    protected string $publicProtectedProtected = 'c';

    /** @DebugIdentifier */
    private string $publicProtectedPrivate = 'c';

    /** @DebugIdentifier */
    private string $publicPrivatePrivate = 'c';

    /** @DebugIdentifier */
    protected string $protectedProtectedProtected = 'c';

    /** @DebugIdentifier */
    private string $protectedProtectedPrivate = 'c';

    /** @DebugIdentifier */
    private string $protectedPrivatePrivate = 'c';

    /** @DebugIdentifier */
    private string $privatePrivatePrivate = 'c';

    /** @DebugIdentifier */
    private static string $staticPrivatePrivatePrivate = 'c';

    /** @DebugIdentifier */
    private string $onlyInC = 'c';

    /** @DebugIdentifier */
    public string $onlyInBAndCPublicPublic = 'c';

    /** @DebugIdentifier */
    protected string $onlyInBAndCPublicProtected = 'c';

    /** @DebugIdentifier */
    private string $onlyInBAndCPublicPrivate = 'c';

    /** @DebugIdentifier */
    protected string $onlyInBAndCProtectedProtected = 'c';

    /** @DebugIdentifier */
    private string $onlyInBAndCProtectedPrivate = 'c';

    /** @DebugIdentifier */
    private string $onlyInBAndCPrivatePrivate = 'c';

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
