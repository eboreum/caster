<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\DateTimeZoneFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DateTimeZoneFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $dateTimeZoneFormatter = new DateTimeZoneFormatter();
        $object = new \stdClass();

        $this->assertFalse($dateTimeZoneFormatter->isHandling($object));
        $this->assertNull($dateTimeZoneFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $dateTimeZoneFormatter = new DateTimeZoneFormatter();

        $object = new \DateTimeZone('+0000');

        $this->assertTrue($dateTimeZoneFormatter->isHandling($object));
        $this->assertSame(
            '\\DateTimeZone (name: "+00:00")',
            $dateTimeZoneFormatter->format($caster, $object),
        );
    }
}
