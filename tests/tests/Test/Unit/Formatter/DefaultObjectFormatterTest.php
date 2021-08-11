<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use PHPUnit\Framework\TestCase;

class DefaultObjectFormatterTest extends TestCase
{
    /**
     * @dataProvider dataProvier_testBasics
     */
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        Caster $caster,
        object $object
    ): void
    {
        $defaultObjectFormatter = new DefaultObjectFormatter;

        $this->assertTrue($defaultObjectFormatter->isHandling($object), $message);

        $this->assertMatchesRegularExpression(
            $expected,
            $defaultObjectFormatter->format($caster, $object),
            $message,
        );

        $caster = $caster->withIsPrependingType(true);

        $this->assertMatchesRegularExpression(
            $expectedWithType,
            $defaultObjectFormatter->format($caster, $object),
            $message,
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: Caster, 4: \stdClass}>
     */
    public function dataProvier_testBasics(): array
    {
        return [
            [
                "stdClass",
                '/^\\\\stdClass$/',
                '/^\\\\stdClass$/',
                Caster::getInstance(),
                new \stdClass,
            ],
        ];
    }
}
