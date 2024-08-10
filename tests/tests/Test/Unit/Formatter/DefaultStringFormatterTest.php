<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Formatter\DefaultStringFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_map;
use function array_merge;
use function assert;
use function chr;
use function implode;
use function is_string;
use function range;

#[CoversClass(DefaultStringFormatter::class)]
class DefaultStringFormatterTest extends TestCase
{
    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: Caster, 4: string}>
     */
    public static function providerTestBasics(): array
    {
        return [
            [
                'foo',
                '/^"foo"$/',
                '/^"foo"$/',
                Caster::getInstance(),
                'foo',
            ],
            [
                'Empty string.',
                '/^""$/',
                '/^""$/',
                Caster::getInstance()->withIsMakingSamples(true),
                '',
            ],
        ];
    }

    /**
     * @return array<int, array{0: string, 1: UnsignedInteger, 2: string}>
     */
    public static function providerTestFormatWorksWithEllipsis(): array
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

    /**
     * @return array<array{string, string, string, string, Caster}>
     */
    public static function providerTestFormatWorksWhenWrapping(): array
    {
        return [
            [
                'An empty string.',
                '""',
                '""',
                '',
                Caster::create(),
            ],
            [
                'A simple "foo" string.',
                '"foo"',
                '"foo"',
                'foo',
                Caster::create(),
            ],
            [
                'A string with one line break at the beginning.',
                "\"\nfoo\"",
                "\"\n    foo\" (indented)",
                "\nfoo",
                Caster::create(),
            ],
            [
                'A string with one line break at the end.',
                "\"foo\n\"",
                "\"foo\n    \" (indented)",
                "foo\n",
                Caster::create(),
            ],
            [
                'A string with several line breaks, both new like (\n) and carriage return (\r).',
                "\"a\nb\rc\r\nd\"",
                "\"a\n    b\n    c\n    d\" (indented)",
                implode('', [
                    'a',
                    "\n",
                    'b',
                    "\r",
                    'c',
                    "\r\n",
                    'd',
                ]),
                Caster::create(),
            ],
            [
                'A string with line break and sampling is disabled.',
                "\"a\nb\"",
                "\"a\n    b\" (indented)",
                "a\nb",
                Caster::create()->withIsMakingSamples(false),
            ],
        ];
    }

    #[DataProvider('providerTestBasics')]
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        Caster $caster,
        string $string,
    ): void {
        $defaultStringFormatter = new DefaultStringFormatter();

        $this->assertTrue($defaultStringFormatter->isHandling($string), $message);

        $formatted = $defaultStringFormatter->format($caster, $string);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression($expected, $formatted, $message);

        $caster = $caster->withIsPrependingType(true);
        $formatted = $defaultStringFormatter->format($caster, $string);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression($expectedWithType, $formatted, $message);
    }

    public function testConvertASCIIControlCharactersToHexAnnotationWorks(): void
    {
        $defaultStringFormatter = new DefaultStringFormatter();

        $value = implode(
            '',
            array_merge(
                ['a'],
                array_map(
                    static function (int $codepoint): string {
                        return chr($codepoint);
                    },
                    range(0, 31),
                ),
                ['b'],
                ["\x7f"],
                ['c'],
            ),
        );

        $this->assertSame(
            implode('', [
                'a',
                '\\x00',
                '\\x01',
                '\\x02',
                '\\x03',
                '\\x04',
                '\\x05',
                '\\x06',
                '\\x07',
                '\\x08',
                '\\x09',
                '\\x0a',
                '\\x0b',
                '\\x0c',
                '\\x0d',
                '\\x0e',
                '\\x0f',
                '\\x10',
                '\\x11',
                '\\x12',
                '\\x13',
                '\\x14',
                '\\x15',
                '\\x16',
                '\\x17',
                '\\x18',
                '\\x19',
                '\\x1a',
                '\\x1b',
                '\\x1c',
                '\\x1d',
                '\\x1e',
                '\\x1f',
                'b',
                '\\x7f',
                'c',
            ]),
            $defaultStringFormatter->convertASCIIControlCharactersToHexAnnotation($value),
        );
    }

    #[DataProvider('providerTestFormatWorksWithEllipsis')]
    public function testFormatWorksCorrectlyWhenApplyingEllipsis(
        string $expected,
        UnsignedInteger $stringSampleSize,
        string $string,
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

    public function testFormatWorksWhenConvertingASCIIControlCharactersToHexAnnotation(): void
    {
        $casterWithoutConversion = Caster::create()->withIsMakingSamples(false);
        $casterWithConversion = $casterWithoutConversion
            ->withIsConvertingASCIIControlCharactersToHexAnnotationInStrings(true);
        $defaultStringFormatter = new DefaultStringFormatter();

        $value = implode(
            '',
            array_merge(
                ['a'],
                array_map(
                    static function (int $codepoint): string {
                        return chr($codepoint);
                    },
                    range(0, 31),
                ),
                ['b'],
                ["\x7f"],
                ['c'],
            ),
        );

        $this->assertSame(
            implode('', [
                '"',
                'a',
                "\x00",
                "\x01",
                "\x02",
                "\x03",
                "\x04",
                "\x05",
                "\x06",
                "\x07",
                "\x08",
                "\x09",
                "\x0a",
                "\x0b",
                "\x0c",
                "\x0d",
                "\x0e",
                "\x0f",
                "\x10",
                "\x11",
                "\x12",
                "\x13",
                "\x14",
                "\x15",
                "\x16",
                "\x17",
                "\x18",
                "\x19",
                "\x1a",
                "\x1b",
                "\x1c",
                "\x1d",
                "\x1e",
                "\x1f",
                'b',
                "\x7f",
                'c',
                '"',
            ]),
            $defaultStringFormatter->format($casterWithoutConversion, $value),
        );

        $expected = implode('', [
            '"',
            'a',
            '\\x00',
            '\\x01',
            '\\x02',
            '\\x03',
            '\\x04',
            '\\x05',
            '\\x06',
            '\\x07',
            '\\x08',
            '\\x09',
            '\\x0a',
            '\\x0b',
            '\\x0c',
            '\\x0d',
            '\\x0e',
            '\\x0f',
            '\\x10',
            '\\x11',
            '\\x12',
            '\\x13',
            '\\x14',
            '\\x15',
            '\\x16',
            '\\x17',
            '\\x18',
            '\\x19',
            '\\x1a',
            '\\x1b',
            '\\x1c',
            '\\x1d',
            '\\x1e',
            '\\x1f',
            'b',
            '\\x7f',
            'c',
            '"',
        ]);

        $this->assertSame($expected, $defaultStringFormatter->format($casterWithConversion, $value));
        $this->assertSame(
            $expected,
            $defaultStringFormatter->format($casterWithConversion->withIsMakingSamples(true), $value),
        );
    }

    #[DataProvider('providerTestFormatWorksWhenWrapping')]
    public function testFormatWorksWhenWrapping(
        string $message,
        string $expectedWithoutWrapping,
        string $expectedWithWrapping,
        string $value,
        Caster $caster,
    ): void {
        $casterWithoutWrapping = $caster->withIsWrapping(false);
        $casterWithWrapping = $casterWithoutWrapping->withIsWrapping(true);
        $defaultStringFormatter = new DefaultStringFormatter();

        $this->assertSame(
            $expectedWithoutWrapping,
            $defaultStringFormatter->format($casterWithoutWrapping, $value),
            $message,
        );
        $this->assertSame(
            $expectedWithWrapping,
            $defaultStringFormatter->format($casterWithWrapping, $value),
            $message,
        );
    }
}
