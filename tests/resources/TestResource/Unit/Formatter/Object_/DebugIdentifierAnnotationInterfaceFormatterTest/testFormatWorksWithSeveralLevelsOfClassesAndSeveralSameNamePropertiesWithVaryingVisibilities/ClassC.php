<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

use Eboreum\Caster\Annotation\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;

abstract class ClassC implements DebugIdentifierAnnotationInterface
{
    /**
     * @DebugIdentifier
     */
    public string $publicPublicPublic = 'c';

    /**
     * @DebugIdentifier
     */
    public string $onlyInBAndCPublicPublic = 'c';

    /**
     * @var mixed
     */
    public $publicDoNotIncludeMe;

    /**
     * @DebugIdentifier
     */
    protected string $publicPublicProtected = 'c';

    /**
     * @DebugIdentifier
     */
    protected string $publicProtectedProtected = 'c';

    /**
     * @DebugIdentifier
     */
    protected string $protectedProtectedProtected = 'c';

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCPublicProtected = 'c';

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCProtectedProtected = 'c';

    /**
     * @var mixed
     */
    protected $protectedDoNotIncludeMe;

    /**
     * @var mixed
     */
    protected $privateDoNotIncludeMe;

    /**
     * @DebugIdentifier
     */
    private string $publicPublicPrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $publicProtectedPrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $publicPrivatePrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $protectedProtectedPrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $protectedPrivatePrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $privatePrivatePrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private static string $staticPrivatePrivatePrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $onlyInC = 'c';

    /**
     * @DebugIdentifier
     */
    private string $onlyInBAndCPublicPrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $onlyInBAndCProtectedPrivate = 'c';

    /**
     * @DebugIdentifier
     */
    private string $onlyInBAndCPrivatePrivate = 'c';
}
