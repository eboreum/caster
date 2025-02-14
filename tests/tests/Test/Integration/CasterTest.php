<?php

declare(strict_types=1);

namespace Test\Integration\Eboreum\Caster;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;
use Eboreum\Caster\Formatter\Object_\ClosureFormatter;
use Eboreum\Caster\Formatter\Object_\DateIntervalFormatter;
use Eboreum\Caster\Formatter\Object_\DatePeriodFormatter;
use Eboreum\Caster\Formatter\Object_\DateTimeInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\DateTimeZoneFormatter;
use Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\DebugInfoFormatter;
use Eboreum\Caster\Formatter\Object_\DirectoryFormatter;
use Eboreum\Caster\Formatter\Object_\PublicVariableFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionClassFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionMethodFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionParameterFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionPropertyFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use Eboreum\Caster\Formatter\Object_\SplFileInfoFormatter;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\ThrowableFormatter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function count;
use function escapeshellarg;
use function shell_exec;
use function sprintf;
use function trim;

#[CoversNothing()]
class CasterTest extends TestCase
{
    /**
     * Also for testing that phpstan.
     */
    public function testWithCustomObjectFormatterCollectionWorks(): void
    {
        $caster = Caster::create();

        $count = shell_exec(
            sprintf(
                'find %s -type f -mindepth 1 -name \'*.php\' | wc -l',
                escapeshellarg(PROJECT_ROOT_DIRECTORY_PATH . '/src/Formatter/Object_'),
            ),
        );

        $this->assertIsString($count);

        $count = trim($count);

        $this->assertMatchesRegularExpression('/^\d+$/D', $count);

        /** @var int $count */
        $count = (int) $count;


        /** @var array<ObjectFormatterInterface> $formatters */
        $formatters = [
            new ClosureFormatter(),
            new DateIntervalFormatter(),
            new DatePeriodFormatter(),
            new DateTimeInterfaceFormatter(),
            new DateTimeZoneFormatter(),
            new DebugIdentifierAttributeInterfaceFormatter(),
            new DebugInfoFormatter(),
            new DirectoryFormatter(),
            new PublicVariableFormatter(),
            new ReflectionAttributeFormatter(),
            new ReflectionClassFormatter(),
            new ReflectionMethodFormatter(),
            new ReflectionParameterFormatter(),
            new ReflectionPropertyFormatter(),
            new ReflectionTypeFormatter(),
            new SplFileInfoFormatter(),
            new TextuallyIdentifiableInterfaceFormatter(),
            new ThrowableFormatter(),
        ];

        $objectFormatterCollection = new ObjectFormatterCollection($formatters);

        $caster = $caster->withCustomObjectFormatterCollection($objectFormatterCollection);

        $this->assertSame($objectFormatterCollection, $caster->getCustomObjectFormatterCollection());
        $this->assertSame($formatters, $caster->getCustomObjectFormatterCollection()->toArray());
        $this->assertSame($count, count($objectFormatterCollection));
    }
}
