<?php

declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Formatter\Object_\DateTimeInterfaceFormatter;
use PHPUnit\Framework\TestCase;

class DateTimeInterfaceFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $dateTimeInterfaceFormatter = new DateTimeInterfaceFormatter;
        $object = new \stdClass;

        $this->assertFalse($dateTimeInterfaceFormatter->isHandling($object));
        $this->assertNull($dateTimeInterfaceFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $dateTimeInterfaceFormatter = new DateTimeInterfaceFormatter;

        $object = new \DateTimeImmutable("2019-01-01T00:00:00+00:00");

        $this->assertTrue($dateTimeInterfaceFormatter->isHandling($object));
        $this->assertSame(
            '\\DateTimeImmutable ("2019-01-01T00:00:00+00:00")',
            $dateTimeInterfaceFormatter->format($caster,  $object),
        );
    }
}
