<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Formatter\DefaultStringFormatter;
use PHPUnit\Framework\TestCase;

class DefaultStringFormatterTest extends TestCase
{
    /**
     * @dataProvider dataProvier_testBasics
     */
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        Caster $caster,
        string $string
    ): void {
        $defaultStringFormatter = new DefaultStringFormatter();

        $this->assertTrue($defaultStringFormatter->isHandling($string), $message);

        $this->assertMatchesRegularExpression(
            $expected,
            $defaultStringFormatter->format($caster, $string),
            $message,
        );

        $caster = $caster->withIsPrependingType(true);

        $this->assertMatchesRegularExpression(
            $expectedWithType,
            $defaultStringFormatter->format($caster, $string),
            $message,
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: Caster, 4: string}>
     */
    public function dataProvier_testBasics(): array
    {
        return [
            [
                'foo',
                '/^"foo"$/',
                '/^"foo"$/',
                Caster::getInstance(),
                'foo',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_testFormatWorksWithEllipsis
     */
    public function testFormatWorksCorrectlyWhenApplyingEllipsis(
        string $expected,
        UnsignedInteger $stringSampleSize,
        string $string
    ): void {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withStringSampleSize($stringSampleSize);
        $defaultStringFormatter = new DefaultStringFormatter();

        $this->assertSame(
            $expected,
            $defaultStringFormatter->format($caster, $string),
        );
    }

    /**
     * @return array<int, array{0: string, 1: UnsignedInteger, 2: string}>
     */
    public function dataProvider_testFormatWorksWithEllipsis(): array
    {
        return [
            [
                '"lo ..." (sample)',
                new UnsignedInteger(6),
                'lorem ipsum',
            ],
            [
                '"..." (sample)',
                new UnsignedInteger(2),
                'lorem ipsum',
            ],
            [
                '"..." (sample)',
                new UnsignedInteger(0),
                'lorem ipsum',
            ],
        ];
    }

    public function testFormatWorksCorrectlyWhenNotMakingSamples(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(false);
        $caster = $caster->withStringSampleSize(new UnsignedInteger(6));
        $defaultStringFormatter = new DefaultStringFormatter();

        $this->assertSame(
            '"lorem ipsum"',
            $defaultStringFormatter->format($caster, 'lorem ipsum'),
        );
    }
}
