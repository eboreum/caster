<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\DatePeriodFormatter;
use PHPUnit\Framework\TestCase;

class DatePeriodFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $datePeriodFormatter = new DatePeriodFormatter();
        $object = new \stdClass();

        $this->assertFalse($datePeriodFormatter->isHandling($object));
        $this->assertNull($datePeriodFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $datePeriodFormatter = new DatePeriodFormatter();

        $object = new \DatePeriod(
            new \DateTimeImmutable('2020-01-01T00:00:00+00:00'),
            new \DateInterval('P1D'),
            new \DateTimeImmutable('2021-01-01T00:00:00+00:00')
        );

        $this->assertTrue($datePeriodFormatter->isHandling($object));
        $this->assertSame(
            implode('', [
                '\\DatePeriod (',
                    'start: \DateTimeImmutable',
                    ', end: \DateTimeImmutable',
                    ', recurrences: null',
                    ', interval: \DateInterval',
                ')',
            ]),
            $datePeriodFormatter->format($caster, $object),
        );
    }
}
