<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

use Eboreum\Caster\Annotation\DebugIdentifier;

abstract class ClassB extends ClassC
{
    /**
     * @DebugIdentifier
     */
    public string $publicPublicPublic = 'b';

    /**
     * @DebugIdentifier
     */
    public string $publicPublicProtected = 'b';

    /**
     * @DebugIdentifier
     */
    public string $publicPublicPrivate = 'b';

    /**
     * @DebugIdentifier
     */
    public string $onlyInBAndCPublicPublic = 'b';

    /**
     * @DebugIdentifier
     */
    public string $onlyInBAndCPublicProtected = 'b';

    /**
     * @DebugIdentifier
     */
    public string $onlyInBAndCPublicPrivate = 'b';

    public $publicDoNotIncludeMe;

    /**
     * @DebugIdentifier
     */
    protected string $publicProtectedProtected = 'b';

    /**
     * @DebugIdentifier
     */
    protected string $publicProtectedPrivate = 'b';

    /**
     * @DebugIdentifier
     */
    protected string $protectedProtectedProtected = 'b';

    /**
     * @DebugIdentifier
     */
    protected string $protectedProtectedPrivate = 'b';

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCProtectedProtected = 'b';

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCProtectedPrivate = 'b';

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCPrivatePrivate = 'b';

    protected $protectedDoNotIncludeMe;

    protected $privateDoNotIncludeMe;

    /**
     * @DebugIdentifier
     */
    private string $publicPrivatePrivate = 'b';

    /**
     * @DebugIdentifier
     */
    private string $protectedPrivatePrivate = 'b';

    /**
     * @DebugIdentifier
     */
    private string $privatePrivatePrivate = 'b';

    /**
     * @DebugIdentifier
     */
    private static string $staticPrivatePrivatePrivate = 'b';

    /**
     * @DebugIdentifier
     */
    private string $onlyInB = 'b';
}
