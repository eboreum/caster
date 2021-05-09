<?php
declare(strict_types = 1);

namespace Eboreum\Caster\TestResource\Unit\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

use Eboreum\Caster\Annotation\DebugIdentifier;

abstract class ClassB extends ClassC
{
    /**
     * @DebugIdentifier
     */
    public string $publicPublicPublic = "b";

    /**
     * @DebugIdentifier
     */
    public string $publicPublicProtected = "b";

    /**
     * @DebugIdentifier
     */
    public string $publicPublicPrivate = "b";

    /**
     * @DebugIdentifier
     */
    protected string $publicProtectedProtected = "b";

    /**
     * @DebugIdentifier
     */
    protected string $publicProtectedPrivate = "b";

    /**
     * @DebugIdentifier
     */
    private string $publicPrivatePrivate = "b";

    /**
     * @DebugIdentifier
     */
    protected string $protectedProtectedProtected = "b";

    /**
     * @DebugIdentifier
     */
    protected string $protectedProtectedPrivate = "b";

    /**
     * @DebugIdentifier
     */
    private string $protectedPrivatePrivate = "b";

    /**
     * @DebugIdentifier
     */
    private string $privatePrivatePrivate = "b";

    /**
     * @DebugIdentifier
     */
    private static string $staticPrivatePrivatePrivate = "b";

    /**
     * @DebugIdentifier
     */
    private string $onlyInB = "b";

    /**
     * @DebugIdentifier
     */
    public string $onlyInBAndCPublicPublic = "b";

    /**
     * @DebugIdentifier
     */
    public string $onlyInBAndCPublicProtected = "b";

    /**
     * @DebugIdentifier
     */
    public string $onlyInBAndCPublicPrivate = "b";

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCProtectedProtected = "b";

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCProtectedPrivate = "b";

    /**
     * @DebugIdentifier
     */
    protected string $onlyInBAndCPrivatePrivate = "b";

    public $publicDoNotIncludeMe;

    protected $protectedDoNotIncludeMe;

    protected $privateDoNotIncludeMe;
}
