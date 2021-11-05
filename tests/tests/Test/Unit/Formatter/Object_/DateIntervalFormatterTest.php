<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\DateIntervalFormatter;
use PHPUnit\Framework\TestCase;

class DateIntervalFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $dateIntervalFormatter = new DateIntervalFormatter();
        $object = new \stdClass();

        $this->assertFalse($dateIntervalFormatter->isHandling($object));
        $this->assertNull($dateIntervalFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $dateIntervalFormatter = new DateIntervalFormatter();

        $object = (new \DateTimeImmutable('2021-01-01T00:00:00+00:00'))->diff(
            new \DateTimeImmutable('2021-02-03T12:34:56+00:00')
        );

        $this->assertTrue($dateIntervalFormatter->isHandling($object));
        $this->assertSame(
            implode('', [
                '\\DateInterval {',
                    '$y = 0',
                    ', $m = 1',
                    ', $d = 2',
                    ', $h = 12',
                    ', $i = 34',
                    ', $s = 56',
                    ', $f = 0',
                    ', $weekday = 0',
                    ', $weekday_behavior = 0',
                    ', $first_last_day_of = 0',
                    ', $invert = 0',
                    ', $days = 33',
                    ', $special_type = 0',
                    ', $special_amount = 0',
                    ', $have_weekday_relative = 0',
                    ', $have_special_relative = 0',
                '}',
            ]),
            $dateIntervalFormatter->format($caster, $object),
        );
    }
}
