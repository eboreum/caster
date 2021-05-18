<?php

declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Formatter\Object_\DirectoryFormatter;
use PHPUnit\Framework\TestCase;

class DirectoryFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $directoryFormatter = new DirectoryFormatter;
        $object = new \stdClass;

        $this->assertFalse($directoryFormatter->isHandling($object));
        $this->assertNull($directoryFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $directoryFormatter = new DirectoryFormatter;

        $object = dir(__DIR__);

        $this->assertTrue($directoryFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            '/^\\\\Directory \{\$path = "(.+)"\}$/',
            $directoryFormatter->format($caster, $object),
        );
    }
}
