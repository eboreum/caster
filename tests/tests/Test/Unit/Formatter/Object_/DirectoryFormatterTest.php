<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Directory;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\DirectoryFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

use function assert;
use function dir;

#[CoversClass(DirectoryFormatter::class)]
class DirectoryFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $directoryFormatter = new DirectoryFormatter();
        $object = new stdClass();

        $this->assertFalse($directoryFormatter->isHandling($object));
        $this->assertNull($directoryFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $directoryFormatter = new DirectoryFormatter();

        $object = dir(__DIR__);

        assert($object instanceof Directory); // Make phpstan happy

        $this->assertTrue($directoryFormatter->isHandling($object));
        $formatted = $directoryFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression('/^\\\\Directory \{\$path = "(.+)"\}$/', $formatted);
    }
}
