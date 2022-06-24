<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultEnumFormatter;
use PHPUnit\Framework\TestCase;
use TestResource\Unit\Eboreum\Caster\Formatter\DefaultEnumFormatterTest\testBasics\IntEnum;
use TestResource\Unit\Eboreum\Caster\Formatter\DefaultEnumFormatterTest\testBasics\StringEnum;
use TestResource\Unit\Eboreum\Caster\Formatter\DefaultEnumFormatterTest\testBasics\UntypedEnum;

class DefaultEnumFormatterTest extends TestCase
{
    /**
     * @dataProvider dataProvier_testBasics
     */
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        string $expectedWithAppendedSplObjectHash,
        Caster $caster,
        object $enum,
    ): void {
        $defaultEnumFormatter = new DefaultEnumFormatter();

        $this->assertTrue($defaultEnumFormatter->isHandling($enum), $message);

        $formatted = $defaultEnumFormatter->format($caster, $enum);
        $this->assertIsString($formatted);
        assert(is_string($formatted));

        $this->assertMatchesRegularExpression($expected, $formatted, $message);

        $caster = $caster->withIsPrependingType(true);
        $formatted = $defaultEnumFormatter->format($caster, $enum);
        $this->assertIsString($formatted);
        assert(is_string($formatted));

        $this->assertMatchesRegularExpression($expectedWithType, $formatted, $message);

        $this->assertFalse($defaultEnumFormatter->isAppendingSplObjectHash());
        $caster = $caster->withIsPrependingType(false);
        $defaultEnumFormatter = $defaultEnumFormatter->withIsAppendingSplObjectHash(true);
        $this->assertTrue($defaultEnumFormatter->isAppendingSplObjectHash());

        $formatted = $defaultEnumFormatter->format($caster, $enum);
        $this->assertIsString($formatted);
        assert(is_string($formatted));

        $this->assertMatchesRegularExpression($expectedWithAppendedSplObjectHash, $formatted, $message);
    }

    /**
     * @return array<int, array{string, string, string, string, Caster, object}>
     */
    public function dataProvier_testBasics(): array
    {
        return [
            [
                StringEnum::class . '::Lorem',
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = "Lorem"',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(StringEnum::class, '/'),
                ),
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = \(string\(5\)\) "Lorem"',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(StringEnum::class, '/'),
                ),
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = "Lorem"',
                        '\} \([0-9a-f]+\)',
                        '$',
                        '/',
                    ]),
                    preg_quote(StringEnum::class, '/'),
                ),
                Caster::getInstance(),
                StringEnum::Lorem,
            ],
            [
                IntEnum::class . '::One',
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = "One"',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(IntEnum::class, '/'),
                ),
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = \(string\(3\)\) "One"',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(IntEnum::class, '/'),
                ),
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = "One"',
                        '\} \([0-9a-f]+\)',
                        '$',
                        '/',
                    ]),
                    preg_quote(IntEnum::class, '/'),
                ),
                Caster::getInstance(),
                IntEnum::One,
            ],
            [
                UntypedEnum::class . '::Hearts',
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = "Hearts"',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(UntypedEnum::class, '/'),
                ),
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = \(string\(6\)\) "Hearts"',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(UntypedEnum::class, '/'),
                ),
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s \{',
                            '\$name = "Hearts"',
                        '\} \([0-9a-f]+\)',
                        '$',
                        '/',
                    ]),
                    preg_quote(UntypedEnum::class, '/'),
                ),
                Caster::getInstance(),
                UntypedEnum::Hearts,
            ],
        ];
    }

    public function testFormatReturnsNullWhenANonEnumObjectIsPassed(): void
    {
        $defaultEnumFormatter = new DefaultEnumFormatter();
        $this->assertNull($defaultEnumFormatter->format(Caster::getInstance(), new \stdClass()));
    }

    public function testIsHandlingReturnsFalseWhenANonEnumObjectIsPassed(): void
    {
        $defaultEnumFormatter = new DefaultEnumFormatter();
        $this->assertFalse($defaultEnumFormatter->isHandling(new \stdClass()));
    }

    public function testWithIsAppendingSplObjectHashWorks(): void
    {
        $caster = Caster::getInstance();
        $enum = UntypedEnum::Hearts;

        $defaultEnumFormatterA = new DefaultEnumFormatter();
        $defaultEnumFormatterB = $defaultEnumFormatterA->withIsAppendingSplObjectHash(false);
        $defaultEnumFormatterC = $defaultEnumFormatterA->withIsAppendingSplObjectHash(true);

        $this->assertNotSame($defaultEnumFormatterA, $defaultEnumFormatterB);
        $this->assertNotSame($defaultEnumFormatterA, $defaultEnumFormatterC);
        $this->assertNotSame($defaultEnumFormatterB, $defaultEnumFormatterC);

        $this->assertFalse($defaultEnumFormatterA->isAppendingSplObjectHash());
        $formatted = $defaultEnumFormatterA->format($caster, $enum);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{\$name = "Hearts"\}',
                    '$',
                    '/',
                ]),
                preg_quote(UntypedEnum::class, '/'),
            ),
            $formatted
        );

        $this->assertFalse($defaultEnumFormatterB->isAppendingSplObjectHash());
        $formatted = $defaultEnumFormatterB->format($caster, $enum);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{\$name = "Hearts"\}',
                    '$',
                    '/',
                ]),
                preg_quote(UntypedEnum::class, '/'),
            ),
            $formatted
        );

        $this->assertTrue($defaultEnumFormatterC->isAppendingSplObjectHash());
        $formatted = $defaultEnumFormatterC->format($caster, $enum);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{\$name = "Hearts"\} \([0-9a-f]+\)',
                    '$',
                    '/',
                ]),
                preg_quote(UntypedEnum::class, '/'),
            ),
            $formatted
        );
    }
}
