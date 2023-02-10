<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities; // phpcs:ignore

use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;

abstract class ClassC implements DebugIdentifierAttributeInterface
{
    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private static string $staticPrivatePrivatePrivate = 'c';

    #[DebugIdentifier]
    public string $publicPublicPublic = 'c';

    #[DebugIdentifier]
    public string $onlyInBAndCPublicPublic = 'c';

    #[DebugIdentifier]
    protected string $publicPublicProtected = 'c';

    #[DebugIdentifier]
    protected string $publicProtectedProtected = 'c';

    #[DebugIdentifier]
    protected string $protectedProtectedProtected = 'c';

    #[DebugIdentifier]
    protected string $onlyInBAndCPublicProtected = 'c';

    #[DebugIdentifier]
    protected string $onlyInBAndCProtectedProtected = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $publicPublicPrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $publicProtectedPrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $publicPrivatePrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $protectedProtectedPrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $protectedPrivatePrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $privatePrivatePrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $onlyInC = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $onlyInBAndCPublicPrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $onlyInBAndCProtectedPrivate = 'c';

    #[DebugIdentifier]
    // @phpstan-ignore-next-line Suppression code babdc1d2; see README.md
    private string $onlyInBAndCPrivatePrivate = 'c';

    /**
     * @var mixed
     */
    public $publicDoNotIncludeMe; // phpcs:ignore

    /**
     * @var mixed
     */
    protected $protectedDoNotIncludeMe; // phpcs:ignore

    /**
     * @var mixed
     */
    protected $privateDoNotIncludeMe; // phpcs:ignore
}
