<?php
declare(strict_types = 1);

namespace Eboreum\Caster\TestResource\Unit\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

use Eboreum\Caster\Annotation\DebugIdentifier;

class ClassA extends ClassB
{
    /**
     * @DebugIdentifier
     */
    public string $publicPublicPublic = "a";

    /**
     * @DebugIdentifier
     */
    public string $publicPublicProtected = "a";

    /**
     * @DebugIdentifier
     */
    public string $publicPublicPrivate = "a";

    /**
     * @DebugIdentifier
     */
    public string $publicProtectedProtected = "a";

    /**
     * @DebugIdentifier
     */
    public string $publicProtectedPrivate = "a";

    /**
     * @DebugIdentifier
     */
    public string $publicPrivatePrivate = "a";

    /**
     * @DebugIdentifier
     */
    protected string $protectedProtectedProtected = "a";

    /**
     * @DebugIdentifier
     */
    protected string $protectedProtectedPrivate = "a";

    /**
     * @DebugIdentifier
     */
    protected string $protectedPrivatePrivate = "a";

    /**
     * @DebugIdentifier
     */
    private string $privatePrivatePrivate = "a";

    /**
     * @DebugIdentifier
     */
    private static string $staticPrivatePrivatePrivate = "a";

    /**
     * @DebugIdentifier
     */
    private string $onlyInA = "a";

    public $publicDoNotIncludeMe;

    protected $protectedDoNotIncludeMe;

    protected $privateDoNotIncludeMe;
}
