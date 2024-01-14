<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Directory;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\DirectoryFormatter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function assert;
use function dir;
use function is_string;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Formatter\Object_\DirectoryFormatter
 */
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
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression('/^\\\\Directory \{\$path = "(.+)"\}$/', $formatted);
    }
}
