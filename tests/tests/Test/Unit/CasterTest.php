<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractEnumFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractStringFormatter;
use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Caster\Context;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Collection\Formatter\EnumFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Common\DataType\String_\Character;
use Eboreum\Caster\Contract\Caster\ContextInterface;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;
use Eboreum\Caster\Contract\Formatter\EnumFormatterInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\EncryptedString;
use Eboreum\Caster\Exception\CasterException;
use Eboreum\Caster\Formatter\DefaultArrayFormatter;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use Eboreum\Caster\Formatter\DefaultResourceFormatter;
use Eboreum\Caster\Formatter\DefaultStringFormatter;
use Eboreum\Caster\Formatter\Object_\DateIntervalFormatter;
use Eboreum\Caster\Formatter\Object_\DatePeriodFormatter;
use Eboreum\Caster\Formatter\Object_\DateTimeInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\DirectoryFormatter;
use Eboreum\Caster\Formatter\Object_\PublicVariableFormatter;
use Eboreum\Caster\Formatter\Object_\SplFileInfoFormatter;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\ThrowableFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TestResource\Unit\Eboreum\Caster\CasterTest\testCastWorks\StringEnum;

use function Eboreum\Caster\functions\is_enum;

class CasterTest extends TestCase
{
    public function testBasics(): void
    {
        $characterEncoding = CharacterEncoding::getInstance();
        $caster = new Caster($characterEncoding);
        $this->assertInstanceOf(Caster::class, $caster);
        $this->assertSame(
            CasterInterface::ARRAY_SAMPLE_SIZE_DEFAULT,
            $caster->getArraySampleSize()->toInteger(),
        );
        $this->assertSame($characterEncoding, $caster->getCharacterEncoding());
        $this->assertCount(0, $caster->getContext());
        $this->assertCount(0, $caster->getCustomArrayFormatterCollection());
        $this->assertCount(0, $caster->getCustomEnumFormatterCollection());
        $this->assertCount(0, $caster->getCustomObjectFormatterCollection());
        $this->assertCount(0, $caster->getCustomResourceFormatterCollection());
        $this->assertCount(0, $caster->getCustomStringFormatterCollection());
        $this->assertInstanceOf(DefaultArrayFormatter::class, $caster->getDefaultArrayFormatter());
        $this->assertInstanceOf(DefaultObjectFormatter::class, $caster->getDefaultObjectFormatter());
        $this->assertInstanceOf(DefaultResourceFormatter::class, $caster->getDefaultResourceFormatter());
        $this->assertInstanceOf(DefaultStringFormatter::class, $caster->getDefaultStringFormatter());
        $this->assertSame(1, $caster->getDepthCurrent()->toInteger());
        $this->assertSame(
            CasterInterface::DEPTH_MAXIMUM_DEFAULT,
            $caster->getDepthMaximum()->toInteger(),
        );
        $this->assertCount(0, $caster->getMaskedEncryptedStringCollection());
        $this->assertSame(
            '*',
            (string)$caster->getMaskingCharacter(),
        );
        $this->assertSame(
            '******',
            $caster->getMaskingString(),
        );
        $this->assertSame(
            6,
            $caster->getMaskingStringLength()->toInteger(),
        );
        $this->assertMatchesRegularExpression(
            implode('', [
                '/',
                '^',
                '\*\* RECURSION \*\* \(',
                    '\\\\stdClass',
                    ', [0-9a-f]{32}',
                '\)',
                '$',
                '/',
            ]),
            $caster->getRecursionMessage(new \stdClass()),
        );
        $this->assertSame(
            CasterInterface::STRING_SAMPLE_SIZE_DEFAULT,
            $caster->getStringSampleSize()->toInteger(),
        );
        $this->assertSame(
            CasterInterface::STRING_QUOTING_CHARACTER_DEFAULT,
            (string)$caster->getStringQuotingCharacter(),
        );
        $this->assertFalse($caster->isPrependingType());
        $this->assertTrue($caster->isMakingSamples());
    }

    public function testCloneWorks(): void
    {
        $characterEncoding = CharacterEncoding::getInstance();
        $caster = new Caster($characterEncoding);

        $clone = clone $caster;
        $this->assertNotSame($caster->getDefaultArrayFormatter(), $clone->getDefaultArrayFormatter());
        $this->assertNotSame($caster->getDefaultObjectFormatter(), $clone->getDefaultObjectFormatter());
        $this->assertNotSame($caster->getDefaultResourceFormatter(), $clone->getDefaultResourceFormatter());
        $this->assertNotSame($caster->getDefaultStringFormatter(), $clone->getDefaultStringFormatter());
    }

    /**
     * @dataProvider dataProvider_testCastWorks
     */
    public function testCastWorks(string $message, string $expected, mixed $value, Caster $caster): void
    {
        $this->assertMatchesRegularExpression(
            $expected,
            $caster->cast($value),
            $message,
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: mixed, 3: Caster}>
     */
    public function dataProvider_testCastWorks(): array
    {
        return [
            [
                'null',
                '/^null$/',
                null,
                Caster::create(),
            ],
            [
                'bool: true',
                '/^true$/',
                true,
                Caster::create(),
            ],
            [
                'bool: false',
                '/^false$/',
                false,
                Caster::create(),
            ],
            [
                'An integer',
                '/^42$/',
                42,
                Caster::create(),
            ],
            [
                'A float',
                '/^3\.14$/',
                3.14,
                Caster::create(),
            ],
            [
                'A string',
                '/^"foo"$/',
                'foo',
                Caster::create(),
            ],
            [
                'object: \stdClass',
                '/^\\\\stdClass$/',
                new \stdClass(),
                Caster::create(),
            ],
            [
                'DateIntervalFormatter',
                implode('', [
                    '/',
                    '^',
                    '\\\\DateInterval \{',
                        '\$y = 1',
                        ', \$m = 1',
                        ', \$d = 2',
                        ', \$h = 12',
                        ', \$i = 34',
                        ', \$s = 56',
                        ', \$f = 0',
                        ', \$weekday = 0',
                        ', \$weekday_behavior = 0',
                        ', \$first_last_day_of = 0',
                        ', \$invert = 0',
                        ', \$days = 399',
                        ', \$special_type = 0',
                        ', \$special_amount = 0',
                        ', \$have_weekday_relative = 0',
                        ', \$have_special_relative = 0',
                    '\}',
                    '$',
                    '/',
                ]),
                (new \DateTimeImmutable('2020-01-01 00:00:00'))->diff(new \DateTimeImmutable('2021-02-03 12:34:56')),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DateIntervalFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DatePeriodFormatter',
                implode('', [
                    '/',
                    '^',
                    '\\\\DatePeriod \(',
                        'start: \\\\DateTimeImmutable',
                        ', end: \\\\DateTimeImmutable',
                        ', recurrences: null',
                        ', interval: \\\\DateInterval',
                    '\)',
                    '$',
                    '/',
                ]),
                new \DatePeriod(
                    new \DateTimeImmutable('2020-01-01 00:00:00'),
                    new \DateInterval('P1D'),
                    new \DateTimeImmutable('2021-02-03 12:34:56'),
                ),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DatePeriodFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DateTimeInterfaceFormatter',
                implode('', [
                    '/',
                    '^',
                    '\\\\DateTimeImmutable \("2021-02-03T12:34:56\+00:00"\)',
                    '$',
                    '/',
                ]),
                new \DateTimeImmutable('2021-02-03 12:34:56+00:00'),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DateTimeInterfaceFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DebugIdentifierAttributeInterfaceFormatter',
                sprintf(
                    '/^class@anonymous\/in\/.+\/%s:\d+ \{\$foo = \(string\(3\)\) "bar"\}$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class implements DebugIdentifierAttributeInterface
                {
                    #[DebugIdentifier]
                    private string $foo = 'bar'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
                },
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DebugIdentifierAttributeInterfaceFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DirectoryFormatter',
                '/^\\\\Directory \{\$path = ".+"\}$/',
                dir(__DIR__),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DirectoryFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'PublicVariableFormatter',
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'class@anonymous\/in\/.+\/%s:\d+ \{',
                            '\$foo = "aaa"',
                            ', \$bar = 42',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class
                {
                    public string $foo = 'aaa';

                    public int $bar = 42;

                    protected ?float $baz = null;

                    /** @var array<mixed> */
                    protected array $bim = []; // phpcs:ignore
                },
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new PublicVariableFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'SplFileInfoFormatter',
                implode('', [
                    '/',
                    '^',
                    '\\\\SplFileObject \(".+"\)',
                    '$',
                    '/',
                ]),
                new \SplFileObject(__FILE__),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new SplFileInfoFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'TextuallyIdentifiableInterfaceFormatter',
                sprintf(
                    '/^class@anonymous\/in\/.+\/%s\:\d+\: AnonymousClass$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class implements TextuallyIdentifiableInterface
                {
                    public function toTextualIdentifier(CasterInterface $caster): string
                    {
                        return 'AnonymousClass';
                    }
                },
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new TextuallyIdentifiableInterfaceFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'ThrowableFormatter',
                implode('', [
                    '/',
                    '^',
                    '\\\\Exception \{',
                        '\$code = 0',
                        ', \$file = ".+"',
                        ', \$line = \d+',
                        ', \$message = "a"',
                        ', \$previous = \\\\RuntimeException \{',
                            '\$code = 1',
                            ', \$file = ".+"',
                            ', \$line = \d+',
                            ', \$message = "b"',
                            ', \$previous = \\\\LogicException \{',
                                '\$code = 2',
                                ', \$file = ".+"',
                                ', \$line = \d+',
                                ', \$message = "c"',
                                ', \$previous = null',
                            '\}',
                        '\}',
                    '\}',
                    '$',
                    '/',
                ]),
                (static function () {
                    $c = new \LogicException('c', 2);
                    $b = new \RuntimeException('b', 1, $c);

                    return new \Exception('a', 0, $b);
                })(),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new ThrowableFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'An array',
                '/^\[0 \=\> "foo", 1 \=\> 42\]$/',
                ['foo', 42],
                Caster::create(),
            ],
            [
                'A resource',
                '/^`stream` Resource id #\d+$/',
                \fopen(__FILE__, 'r+'),
                Caster::create(),
            ],
            (static function(){
                $class = new class extends \DateTime
                {
                };
                class_alias($class::class, 'FooBar_9f8a3c814a1d42dda2672abede7ce454');

                $caster = Caster::create();
                $caster = $caster->withCustomObjectFormatterCollection(
                    new ObjectFormatterCollection([
                        new DateTimeInterfaceFormatter(),
                    ]),
                );

                return [
                    'class_alias(...) works.',
                    sprintf(
                        implode('', [
                            '/',
                            '^',
                            '\\\\DateTime@anonymous\/in\/.+\/%s\:\d+ \("2022-01-01T00:00:00\+00:00"\)',
                            '$',
                            '/',
                        ]),
                        preg_quote(basename(__FILE__), '/'),
                    ),
                    new \FooBar_9f8a3c814a1d42dda2672abede7ce454( // @phpstan-ignore-line It is being aliased above
                        '2022-01-01T00:00:00.000000+00:00'
                    ),
                    $caster,
                ];
            })(),
            [
                'An array',
                '/^\[0 \=\> "foo", 1 \=\> 42\]$/',
                ['foo', 42],
                Caster::create(),
            ],
            [
                'A resource',
                '/^`stream` Resource id #\d+$/',
                \fopen(__FILE__, 'r+'),
                Caster::create(),
            ],
            [
                'An enum',
                sprintf(
                    '/^\\\\%s \{\$name = "Lorem"\}$/',
                    preg_quote(StringEnum::class, '/'),
                ),
                StringEnum::Lorem,
                Caster::create(),
            ],
        ];
    }

    public function testCastWorksWithStringSample(): void
    {
        $str = str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1);

        $this->assertSame(
            '"' . str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT - 4) . ' ..." (sample)',
            Caster::create()->withIsMakingSamples(true)->cast($str),
        );
    }

    public function testCastWorksWithoutStringSample(): void
    {
        $str = str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1);

        $this->assertSame(
            '"' . str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1) . '"',
            Caster::create()->withIsMakingSamples(false)->cast($str),
        );
    }

    public function testCastWorksWithAnonymousClass(): void
    {
        $class = new class
        {
        };

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s:\d+',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            Caster::create()->cast($class),
        );
    }

    public function testCastWorksWithResource(): void
    {
        $this->assertMatchesRegularExpression(
            '/^`stream` Resource id #\d+$/',
            Caster::create()->cast(fopen(__FILE__, 'r+')),
        );
    }

    public function testCastWorksWithArrayAndWithSampling(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withArraySampleSize(new UnsignedInteger(3));
        $caster = $caster->withStringSampleSize(new UnsignedInteger(5));
        $array = [
            'foobar',
            'loremipsum' => 'dolorsit', // phpcs:ignore
            1,
            2,
            3,
        ];

        $this->assertSame(
            implode('', [
                '[',
                '0 => "f ..." (sample)',
                ', "l ..." (sample) => "d ..." (sample)',
                ', 1 => 1, ... and 2 more elements',
                '] (sample)',
            ]),
            $caster->cast($array),
        );
    }

    public function testCastWorksWithArrayButWithoutSampling(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withArraySampleSize(new UnsignedInteger(10));
        $caster = $caster->withStringSampleSize(new UnsignedInteger(200));
        $array = [
            'foobar',
            'loremipsum' => 'dolorsit', // phpcs:ignore
            1,
            2,
            3,
        ];

        $this->assertSame(
            '[0 => "foobar", "loremipsum" => "dolorsit", 1 => 1, 2 => 2, 3 => 3]',
            $caster->cast($array),
        );
    }

    /**
     * @dataProvider dataProvider_testCastWorksWithArrayLargerThanSampleSize
     * @param array<mixed> $array
     */
    public function testCastWorksWithArrayLargerThanSampleSize(string $message, string $expected, array $array): void
    {
        $this->assertSame(
            $expected,
            Caster::create()->withIsMakingSamples(true)->cast($array),
            $message,
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, array<mixed>}>
     */
    public function dataProvider_testCastWorksWithArrayLargerThanSampleSize(): array
    {
        return [
            [
                'Singular "element"',
                '[0 => "foo", 1 => 42, 2 => null, ... and 1 more element] (sample)',
                ['foo', 42, null, false]
            ],
            [
                'Plural "elements"',
                '[0 => 1, 1 => 1, 2 => 1, ... and 97 more elements] (sample)',
                array_fill(0, 100, 1),
            ],
        ];
    }

    public function testCastWorksWithAnAssociativeArray(): void
    {
        $array = ['foo' => 1, 'bar' => 2, 'baz' => 3, 'bim' => 4];

        $this->assertSame(
            '["foo" => 1, "bar" => 2, "baz" => 3, ... and 1 more element] (sample)',
            Caster::create()->withIsMakingSamples(true)->cast($array),
        );
    }

    public function testCastWorksWithAMixedArray(): void
    {
        $array = ['foo', 'bar' => 2, 'baz', 'bim' => 4];

        $this->assertSame(
            '[0 => "foo", "bar" => 2, 1 => "baz", ... and 1 more element] (sample)',
            Caster::create()->withIsMakingSamples(true)->cast($array),
        );
    }

    public function testCastWorksWithMaskedStrings(): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString('bar'),
            new EncryptedString('bim'),
        ]));

        $this->assertSame(
            sprintf(
                '"foo %s baz %s" (masked)',
                '******',
                '******'
            ),
            $caster->cast('foo bar baz bim'),
        );
    }

    public function testCastWorksWithMaskedStringsAndSimplifying(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withStringSampleSize(new UnsignedInteger(10));
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString('bar'),
            new EncryptedString('bim'),
        ]));

        $this->assertSame(
            '"foo ** ..." (sample) (masked)',
            $caster->cast('foo bar baz bim'),
        );
    }

    public function testCastWillCorrectlyMaskArrayKeys(): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString('bar'),
            new EncryptedString('bim'),
        ]));
        $array = ['foo bar baz bim' => 'bar'];

        // It's the masked length = 19, not the original length. Don't bleed information about masked string
        $this->assertSame(
            sprintf(
                '["foo %s baz %s" (masked) => "%s" (masked)]',
                '******',
                '******',
                '******',
            ),
            $caster->cast($array),
        );
    }

    /**
     * @dataProvider dataProvider_testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings
     * @param EncryptedStringCollection<EncryptedString> $encryptedStringCollection
     */
    public function testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings(
        string $expected,
        string $input,
        EncryptedStringCollection $encryptedStringCollection
    ): void {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection($encryptedStringCollection);

        $this->assertSame($expected, $caster->cast($input));
    }

    /**
     * @return array<int, array{string, string, EncryptedStringCollection<EncryptedString>}>
     */
    public function dataProvider_testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings(): array
    {
        return [
            [
                sprintf(
                    '"foo %s baz %s bim" (masked)',
                    '******',
                    '******',
                ),
                'foo bar baz *** bim',
                new EncryptedStringCollection([
                    new EncryptedString('***'),
                    new EncryptedString('bar'),
                ]),
            ],
            [
                sprintf(
                    '"foo %s baz %s bim" (masked)',
                    '******',
                    '******',
                ),
                'foo bar baz *** bim',
                new EncryptedStringCollection([
                    new EncryptedString('bar'),
                    new EncryptedString('***'),
                ]),
            ],
            [
                sprintf(
                    '"foo %s %s baz bim" (masked)',
                    '******',
                    '******',
                ),
                'foo *** bar baz bim',
                new EncryptedStringCollection([
                    new EncryptedString('***'),
                    new EncryptedString('bar'),
                ]),
            ],
            [
                sprintf(
                    '"foo %s %s baz bim" (masked)',
                    '******',
                    '******',
                ),
                'foo *** bar baz bim',
                new EncryptedStringCollection([
                    new EncryptedString('bar'),
                    new EncryptedString('***'),
                ]),
            ],
            [
                sprintf(
                    '"foo %s bar" (masked)',
                    '******',
                ),
                'foo ********** bar',
                new EncryptedStringCollection([
                    new EncryptedString('***'),
                    new EncryptedString('**********'),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_testCastWorksWithTypePrepended
     */
    public function testCastWorksWithTypePrepended(string $message, string $expected, mixed $value, Caster $caster): void
    {
        $caster = $caster->withIsPrependingType(true);

        $this->assertMatchesRegularExpression(
            $expected,
            $caster->cast($value),
            $message,
        );

        $casterWithoutIsPrepepndingType = $caster->withIsPrependingType(false);

        $this->assertMatchesRegularExpression(
            $expected,
            $casterWithoutIsPrepepndingType->castTyped($value),
            $message,
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: mixed, 3: Caster}>
     */
    public function dataProvider_testCastWorksWithTypePrepended(): array
    {
        return [
            [
                'null',
                '/^\(null\) null$/',
                null,
                Caster::create(),
            ],
            [
                'bool: true',
                '/^\(bool\) true$/',
                true,
                Caster::create(),
            ],
            [
                'bool: false',
                '/^\(bool\) false$/',
                false,
                Caster::create(),
            ],
            [
                'An integer',
                '/^\(int\) 42$/',
                42,
                Caster::create(),
            ],
            [
                'A float',
                '/^\(float\) 3\.14$/',
                3.14,
                Caster::create(),
            ],
            [
                'A string',
                '/^\(string\(3\)\) "foo"$/',
                'foo',
                Caster::create(),
            ],
            [
                'object: \stdClass',
                '/^\(object\) \\\\stdClass$/',
                new \stdClass(),
                Caster::create(),
            ],
            [
                'DateIntervalFormatter',
                implode('', [
                    '/',
                    '^',
                    '\(object\) \\\\DateInterval \{',
                        '\$y = \(int\) 1',
                        ', \$m = \(int\) 1',
                        ', \$d = \(int\) 2',
                        ', \$h = \(int\) 12',
                        ', \$i = \(int\) 34',
                        ', \$s = \(int\) 56',
                        ', \$f = \(float\) 0',
                        ', \$weekday = \(int\) 0',
                        ', \$weekday_behavior = \(int\) 0',
                        ', \$first_last_day_of = \(int\) 0',
                        ', \$invert = \(int\) 0',
                        ', \$days = \(int\) 399',
                        ', \$special_type = \(int\) 0',
                        ', \$special_amount = \(int\) 0',
                        ', \$have_weekday_relative = \(int\) 0',
                        ', \$have_special_relative = \(int\) 0',
                    '\}',
                    '$',
                    '/',
                ]),
                (new \DateTimeImmutable('2020-01-01 00:00:00'))->diff(new \DateTimeImmutable('2021-02-03 12:34:56')),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DateIntervalFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DatePeriodFormatter',
                implode('', [
                    '/',
                    '^',
                    '\(object\) \\\\DatePeriod \(',
                        'start: \(object\) \\\\DateTimeImmutable',
                        ', end: \(object\) \\\\DateTimeImmutable',
                        ', recurrences: \(null\) null',
                        ', interval: \(object\) \\\\DateInterval',
                    '\)',
                    '$',
                    '/',
                ]),
                new \DatePeriod(
                    new \DateTimeImmutable('2020-01-01 00:00:00'),
                    new \DateInterval('P1D'),
                    new \DateTimeImmutable('2021-02-03 12:34:56'),
                ),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DatePeriodFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DateTimeInterfaceFormatter',
                implode('', [
                    '/',
                    '^',
                    '\(object\) \\\\DateTimeImmutable \("2021-02-03T12:34:56\+00:00"\)',
                    '$',
                    '/',
                ]),
                new \DateTimeImmutable('2021-02-03 12:34:56+00:00'),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DateTimeInterfaceFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DebugIdentifierAttributeInterfaceFormatter',
                sprintf(
                    '/^\(object\) class@anonymous\/in\/.+\/%s:\d+ \{\$foo = \(string\(3\)\) "bar"\}$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class implements DebugIdentifierAttributeInterface
                {
                    #[DebugIdentifier]
                    private string $foo = 'bar'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
                },
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DebugIdentifierAttributeInterfaceFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'DirectoryFormatter',
                '/^\(object\) \\\\Directory \{\$path = \(string\(\d+\)\) ".+"\}$/',
                dir(__DIR__),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new DirectoryFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'PublicVariableFormatter',
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\(object\) class@anonymous\/in\/.+\/%s:\d+ \{',
                            '\$foo = \(string\(3\)\) "aaa"',
                            ', \$bar = \(int\) 42',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class
                {
                    public string $foo = 'aaa';

                    public int $bar = 42;

                    protected ?float $baz = null;

                    /** @var array<mixed> */
                    protected array $bim = []; // phpcs:ignore
                },
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new PublicVariableFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'SplFileInfoFormatter',
                implode('', [
                    '/',
                    '^',
                    '\(object\) \\\\SplFileObject \(".+"\)',
                    '$',
                    '/',
                ]),
                new \SplFileObject(__FILE__),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new SplFileInfoFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'TextuallyIdentifiableInterfaceFormatter',
                sprintf(
                    '/^\(object\) class@anonymous\/in\/.+\/%s\:\d+\: AnonymousClass$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class implements TextuallyIdentifiableInterface
                {
                    public function toTextualIdentifier(CasterInterface $caster): string
                    {
                        return 'AnonymousClass';
                    }
                },
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new TextuallyIdentifiableInterfaceFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'ThrowableFormatter',
                implode('', [
                    '/',
                    '^',
                    '\(object\) \\\\Exception \{',
                        '\$code = \(int\) 0',
                        ', \$file = \(string\(\d+\)\) ".+"',
                        ', \$line = \(int\) \d+',
                        ', \$message = \(string\(\d+\)\) "a"',
                        ', \$previous = \(object\) \\\\RuntimeException \{',
                            '\$code = \(int\) 1',
                            ', \$file = \(string\(\d+\)\) ".+"',
                            ', \$line = \(int\) \d+',
                            ', \$message = \(string\(\d+\)\) "b"',
                            ', \$previous = \(object\) \\\\LogicException \{',
                                '\$code = \(int\) 2',
                                ', \$file = \(string\(\d+\)\) ".+"',
                                ', \$line = \(int\) \d+',
                                ', \$message = \(string\(\d+\)\) "c"',
                                ', \$previous = \(null\) null',
                            '\}',
                        '\}',
                    '\}',
                    '$',
                    '/',
                ]),
                (static function () {
                    $c = new \LogicException('c', 2);
                    $b = new \RuntimeException('b', 1, $c);

                    return new \Exception('a', 0, $b);
                })(),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection([
                            new ThrowableFormatter(),
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                'An array',
                implode('', [
                    '/',
                    '^',
                    '\(array\(2\)\) \[',
                        '\(int\) 0 \=\> \(string\(\d+\)\) "foo"',
                        ', \(int\) 1 \=\> \(int\) 42',
                    '\]',
                    '$',
                    '/',
                ]),
                ['foo', 42],
                Caster::create(),
            ],
            [
                'A resource',
                '/^\(resource\) `stream` Resource id #\d+$/',
                \fopen(__FILE__, 'r+'),
                Caster::create(),
            ],
        ];
    }

    public function testCastWorksWithCustomFormatters(): void
    {
        $caster = Caster::create();
        $caster = $caster->withCustomArrayFormatterCollection(new ArrayFormatterCollection([
            new class extends AbstractArrayFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, array $array): ?string
                {
                    if (false === $this->isHandling($array)) {
                        return null;
                    }

                    $array['replaceme'] = 'replaced';

                    return $caster->getDefaultArrayFormatter()->format($caster, $array);
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(array $array): bool
                {
                    return array_key_exists('replaceme', $array);
                }
            },
        ]));
        $caster = $caster->withCustomEnumFormatterCollection(new EnumFormatterCollection([
            new class extends AbstractObjectFormatter implements EnumFormatterInterface
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, object $enum): ?string
                {
                    if (false === $this->isHandling($enum)) {
                        return null;
                    }

                    assert($enum instanceof \DateTimeInterface); // Make phpstan happy

                    return sprintf(
                        '\\%s {$name = %s, $value = %s}',
                        $enum::class,
                        $caster->cast($enum->name), // @phpstan-ignore-line
                        $caster->cast($enum->value), // @phpstan-ignore-line
                    );
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(object $enum): bool
                {
                    if (is_enum($enum)) {
                        $reflectionEnum = new \ReflectionEnum($enum);
                        $reflectionType = $reflectionEnum->getBackingType();

                        if ($reflectionType) {
                            /**
                             * PHPStan is clearly confused here. It is true that it is not listed here ???
                             * https://www.php.net/manual/en/class.reflectiontype.php ??? but the method `getName` does in
                             * fact exist.
                             */

                            // @phpstan-ignore-next-line
                            return 'string' === $reflectionType->getName();
                        }
                    }

                    return false;
                }
            },
        ]));
        $caster = $caster->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            new class extends AbstractObjectFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, object $object): ?string
                {
                    if (false === $this->isHandling($object)) {
                        return null;
                    }

                    assert($object instanceof \DateTimeInterface); // Make phpstan happy

                    return sprintf(
                        '\\%s (%s)',
                        $object::class,
                        $object->format('c')
                    );
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(object $object): bool
                {
                    return ($object instanceof \DateTimeInterface);
                }
            },
            new class extends AbstractObjectFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, object $object): ?string
                {
                    if (false === $this->isHandling($object)) {
                        return null;
                    }

                    assert($object instanceof \Throwable); // Make phpstan happy

                    return sprintf(
                        '\\%s {$code = %s, $file = %s, $line = %s, $message = %s}',
                        $object::class,
                        $caster->cast($object->getCode()),
                        $caster->cast($object->getFile()),
                        $caster->cast($object->getLine()),
                        $caster->cast($object->getMessage())
                    );
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(object $object): bool
                {
                    return ($object instanceof \Throwable);
                }
            },
        ]));
        $caster = $caster->withCustomResourceFormatterCollection(new ResourceFormatterCollection([
            new class extends AbstractResourceFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, Resource_ $resource): ?string
                {
                    if (false === $this->isHandling($resource)) {
                        return null;
                    }

                    return 'YOLO';
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(Resource_ $resource): bool
                {
                    return ('stream' === get_resource_type($resource->getResource()));
                }
            },
        ]));
        $caster = $caster->withCustomStringFormatterCollection(new StringFormatterCollection([
            new class extends AbstractStringFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, string $string): ?string
                {
                    if (false === $this->isHandling($string)) {
                        return null;
                    }

                    return $caster->getDefaultStringFormatter()->format($caster, 'bar');
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(string $string): bool
                {
                    return ('foo' === $string);
                }
            },
        ]));
        $this->assertSame('[0 => 1]', $caster->cast([1]));
        $this->assertSame('["replaceme" => "replaced"]', $caster->cast(['replaceme' => 'original']));
        $this->assertSame(
            sprintf(
                '\\%s {$name = "Lorem", $value = "Lorem"}',
                StringEnum::class,
            ),
            $caster->cast(StringEnum::Lorem),
        );
        $this->assertSame('\\stdClass', $caster->cast(new \stdClass()));
        $this->assertSame(
            '\\DateTimeImmutable (2019-01-01T00:00:00+00:00)',
            $caster->cast(new \DateTimeImmutable('2019-01-01T00:00:00+00:00')),
        );
        $this->assertMatchesRegularExpression(
            '/^\\\\RuntimeException \{\$code = 1, \$file = "(.+)", \$line = \d+, \$message = "test"\}$/',
            $caster->cast(new \RuntimeException('test', 1))
        );
        $this->assertSame('YOLO', $caster->cast(\fopen(__FILE__, 'r+')));
        $this->assertSame('"baz"', $caster->cast('baz'));
        $this->assertSame('"bar"', $caster->cast('foo'));
    }

    public function testCastWorksWithPrependedTypeAndWithStringSample(): void
    {
        $str = str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1);

        $this->assertSame(
            sprintf(
                '(string(%d)) "%s ..." (sample)',
                (CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1),
                str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT - 4)
            ),
            Caster::create()->withIsMakingSamples(true)->withIsPrependingType(true)->cast($str),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithoutStringSample(): void
    {
        $str = str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1);

        $this->assertSame(
            sprintf(
                '(string(%d)) "%s"',
                (CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1),
                str_repeat('a', CasterInterface::STRING_SAMPLE_SIZE_DEFAULT + 1)
            ),
            Caster::create()->withIsMakingSamples(false)->withIsPrependingType(true)->cast($str),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithAnonymousClass(): void
    {
        $class = new class
        {
        };

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\(object\) class@anonymous\/in\/.+\/%s:\d+',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            Caster::create()->withIsPrependingType(true)->cast($class),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithResource(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\(resource\) `stream` Resource id #\d+$/',
            Caster::create()->withIsPrependingType(true)->cast(fopen(__FILE__, 'r+')),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithArrayAndWithSampling(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withArraySampleSize(new UnsignedInteger(3));
        $caster = $caster->withStringSampleSize(new UnsignedInteger(5));
        $array = [
            'foobar',
            'loremipsum' => 'dolorsit', // phpcs:ignore
            1,
            2,
            3,
        ];

        $this->assertSame(
            implode('', [
                '(array(5)) [',
                    '(int) 0 => (string(6)) "f ..." (sample),',
                    ' (string(10)) "l ..." (sample) => (string(8)) "d ..." (sample)',
                    ', (int) 1 => (int) 1',
                    ', ... and 2 more elements',
                '] (sample)',
            ]),
            $caster->withIsPrependingType(true)->cast($array),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithArrayButWithoutSampling(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withArraySampleSize(new UnsignedInteger(10));
        $caster = $caster->withStringSampleSize(new UnsignedInteger(200));
        $array = [
            'foobar',
            'loremipsum' => 'dolorsit', // phpcs:ignore
            1,
            2,
            3,
        ];

        $this->assertSame(
            implode('', [
                '(array(5)) [',
                    '(int) 0 => (string(6)) "foobar"',
                    ', (string(10)) "loremipsum" => (string(8)) "dolorsit"',
                    ', (int) 1 => (int) 1',
                    ', (int) 2 => (int) 2',
                    ', (int) 3 => (int) 3',
                ']',
            ]),
            $caster->withIsPrependingType(true)->cast($array),
        );
    }

    /**
     * @dataProvider dataProvider_testCastWorksWithPrependedTypeAndWithArrayLargerThanSampleSize
     * @param array<mixed> $array
     */
    public function testCastWorksWithPrependedTypeAndWithArrayLargerThanSampleSize(
        string $message,
        string $expected,
        array $array
    ): void {
        $this->assertSame(
            $expected,
            Caster::create()->withIsMakingSamples(true)->withIsPrependingType(true)->cast($array),
            $message,
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: array<mixed>}>
     */
    public function dataProvider_testCastWorksWithPrependedTypeAndWithArrayLargerThanSampleSize(): array
    {
        return [
            [
                'Singular "element"',
                implode('', [
                    '(array(4)) [(int) 0 => (string(3)) "foo", (int) 1 => (int) 42, (int) 2 => (null) null',
                    ', ... and 1 more element] (sample)',
                ]),
                ['foo', 42, null, false],
            ],
            [
                'Plural "elements"',
                implode('', [
                    '(array(100)) [(int) 0 => (int) 1, (int) 1 => (int) 1, (int) 2 => (int) 1',
                    ', ... and 97 more elements] (sample)',
                ]),
                array_fill(0, 100, 1),
            ],
        ];
    }

    public function testCastWorksWithPrependedTypeAndWithAnAssociativeArray(): void
    {
        $array = ['foo' => 1, 'bar' => 2, 'baz' => 3, 'bim' => 4];

        $this->assertSame(
            implode('', [
                '(array(4)) [',
                    '(string(3)) "foo" => (int) 1',
                    ', (string(3)) "bar" => (int) 2',
                    ', (string(3)) "baz" => (int) 3',
                    ', ... and 1 more element',
                '] (sample)',
            ]),
            Caster::create()->withIsMakingSamples(true)->withIsPrependingType(true)->cast($array),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithAMixedArray(): void
    {
        $array = ['foo', 'bar' => 2, 'baz', 'bim' => 4];

        $this->assertSame(
            implode('', [
                '(array(4)) [',
                '(int) 0 => (string(3)) "foo"',
                ', (string(3)) "bar" => (int) 2',
                ', (int) 1 => (string(3)) "baz"',
                ', ... and 1 more element',
                '] (sample)',
            ]),
            Caster::create()->withIsMakingSamples(true)->withIsPrependingType(true)->cast($array),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithMaskedStrings(): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString('bar'),
            new EncryptedString('bim'),
        ]));

        $this->assertSame(
            sprintf(
                '(string(21)) "foo %s baz %s" (masked)',
                '******',
                '******',
            ),
            $caster->withIsPrependingType(true)->cast('foo bar baz bim'),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithMaskedStringsAndSimplifying(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withStringSampleSize(new UnsignedInteger(10));
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString('bar'),
            new EncryptedString('bim'),
        ]));

        $this->assertSame(
            '(string(21)) "foo ** ..." (sample) (masked)',
            $caster->withIsPrependingType(true)->cast('foo bar baz bim'),
        );
    }

    public function testCastWorksWithPrependedTypeAndWillCorrectlyMaskArrayKeys(): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString('bar'),
            new EncryptedString('bim'),
        ]));
        $array = ['foo bar baz bim' => 'bar'];

        // It's the masked length = 19, not the original length. Don't bleed information about masked string
        $this->assertSame(
            sprintf(
                '(array(1)) [(string(21)) "foo %s baz %s" (masked) => (string(6)) "%s" (masked)]',
                '******',
                '******',
                '******',
            ),
            $caster->withIsPrependingType(true)->cast($array),
        );
    }

    public function testCastWorksWhenArrayIsBeingOmitted(): void
    {
        $caster = Caster::create();
        $caster = $caster->withDepthMaximum(new PositiveInteger(2));

        $array = [
            'foo' => [
                'bar' => [
                    'baz' => [],
                ],
            ],
        ];

        $this->assertSame(
            sprintf(
                '["foo" => ["bar" => [%s] ** OMITTED ** (maximum depth of 2 reached)]]',
                $caster->getSampleEllipsis(),
            ),
            $caster->cast($array),
        );

        $caster = $caster->withIsPrependingType(true);

        $this->assertSame(
            sprintf(
                implode('', [
                    '(array(1)) [',
                        '(string(3)) "foo" => (array(1)) [',
                            '(string(3)) "bar" => (array(1)) [%s] ** OMITTED ** (maximum depth of 2 reached)',
                        ']',
                    ']',
                ]),
                $caster->getSampleEllipsis(),
            ),
            $caster->cast($array),
        );
    }

    public function testCastWillHandleObjectRecursionCorrectly(): void
    {
        $caster = Caster::create();
        $object = new \stdClass();

        $context = $this->mockContextInterface();

        $context
            ->expects($this->exactly(2))
            ->method('hasVisitedObject')
            ->with($object)
            ->willReturn(true);

        $caster = $caster->withContext($context);

        $this->assertSame(
            $caster->getRecursionMessage($object),
            $caster->cast($object),
        );

        $this->assertSame(
            sprintf(
                '(object) %s',
                $caster->getRecursionMessage($object),
            ),
            $caster->castTyped($object),
        );
    }

    public function testCastWillHandleReachingDepthMaximumCorrectly(): void
    {
        $caster = Caster::create();
        $caster = $caster->withDepthMaximum(new PositiveInteger(1));
        $caster = $caster->withDepthCurrent(new PositiveInteger(2));
        $object = new \stdClass();

        $this->assertSame(
            sprintf(
                '\\stdClass: %s',
                $caster->getOmittedMaximumDepthOfXReachedMessage(),
            ),
            $caster->cast($object),
        );

        $this->assertSame(
            sprintf(
                '(object) \\stdClass: %s',
                $caster->getOmittedMaximumDepthOfXReachedMessage(),
            ),
            $caster->castTyped($object),
        );
    }

    /**
     * @dataProvider dataProvider_testEscapeWorks
     */
    public function testEscapeWorks(string $expected, string $str): void
    {
        $this->assertSame($expected, Caster::create()->escape($str));
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public function dataProvider_testEscapeWorks(): array
    {
        return [
            ['\\\\', '\\'],
            ['\\"', '"'],
            ['\\\\\\"', '\\"'],
            ['\\\\foo\\"', '\\foo"'],
        ];
    }

    /**
     * @dataProvider dataProvider_testMaskStringWorks
     */
    public function testMaskStringWorks(string $message, string $expected, Caster $caster, string $str): void
    {
        $this->assertSame($expected, $caster->maskString($str), $message);
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: Caster, 3: string}>
     */
    public function dataProvider_testMaskStringWorks(): array
    {
        return [
            [
                '',
                '',
                Caster::create(),
                '',
            ],
            [
                '',
                'foo bar baz',
                Caster::create(),
                'foo bar baz',
            ],
            [
                '',
                sprintf(
                    'foo %s baz',
                    '******',
                ),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
                        new EncryptedString('bar'),
                    ]));

                    return $caster;
                })(),
                'foo bar baz',
            ],
            [
                '',
                sprintf(
                    '12%s78',
                    '******',
                ),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
                        new EncryptedString('3'),
                        new EncryptedString('34'),
                        new EncryptedString('345'),
                        new EncryptedString('3456'),
                    ]));

                    return $caster;
                })(),
                '12345678',
            ],
            [
                'It works with overlapping masking strings',
                sprintf(
                    '12%s67',
                    '******',
                ),
                (static function () {
                    $caster = Caster::create();
                    $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
                        new EncryptedString('34'),
                        new EncryptedString('45'),
                    ]));

                    return $caster;
                })(),
                '1234567',
            ],
        ];
    }

    public function testQuoteAndEscapeWorks(): void
    {
        $this->assertSame('"\\\\foo\\""', Caster::create()->quoteAndEscape('\\foo"'));
    }

    public function testWithArraySampleSizeWorks(): void
    {
        $casterA = Caster::create();

        $casterB = $casterA->withArraySampleSize(new UnsignedInteger(1));

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getArraySampleSize(),
            $casterB->getArraySampleSize(),
        );
        $this->assertSame(
            CasterInterface::ARRAY_SAMPLE_SIZE_DEFAULT,
            $casterA->getArraySampleSize()->toInteger(),
        );
        $this->assertSame(
            1,
            $casterB->getArraySampleSize()->toInteger(),
        );
    }

    public function testWithCharacterEncodingCollectionWorks(): void
    {
        $casterA = Caster::create();
        $characterEncodingA = $casterA->getCharacterEncoding();

        $characterEncodingB = new CharacterEncoding('ISO-8859-1');
        $casterB = $casterA->withCharacterEncoding($characterEncodingB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getCharacterEncoding(),
            $casterB->getCharacterEncoding(),
        );
        $this->assertSame(
            $characterEncodingA,
            $casterA->getCharacterEncoding(),
        );
        $this->assertSame(
            $characterEncodingB,
            $casterB->getCharacterEncoding(),
        );
    }

    public function testWithContextWorks(): void
    {
        $casterA = Caster::create();
        $contextA = $casterA->getContext();

        $contextB = new Context();
        $casterB = $casterA->withContext($contextB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getContext(),
            $casterB->getContext(),
        );
        $this->assertSame(
            $contextA,
            $casterA->getContext(),
        );
        $this->assertSame(
            $contextB,
            $casterB->getContext(),
        );
    }

    public function testWithCustomArrayFormatterCollectionWorks(): void
    {
        $casterA = Caster::create();
        $arrayFormatterCollectionA = $casterA->getCustomArrayFormatterCollection();

        $arrayFormatterCollectionB = new ArrayFormatterCollection([
            new class extends AbstractArrayFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, array $array): ?string
                {
                    return null;
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(array $array): bool
                {
                    return true;
                }
            }
        ]);
        $casterB = $casterA->withCustomArrayFormatterCollection($arrayFormatterCollectionB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getCustomArrayFormatterCollection(),
            $casterB->getCustomArrayFormatterCollection(),
        );
        $this->assertSame(
            $arrayFormatterCollectionA,
            $casterA->getCustomArrayFormatterCollection(),
        );
        $this->assertCount(
            0,
            $casterA->getCustomArrayFormatterCollection(),
        );
        $this->assertSame(
            $arrayFormatterCollectionB,
            $casterB->getCustomArrayFormatterCollection(),
        );
        $this->assertCount(
            1,
            $casterB->getCustomArrayFormatterCollection(),
        );
    }

    public function testWithCustomEnumFormatterCollectionWorks(): void
    {
        $casterA = Caster::create();
        $enumFormatterCollectionA = $casterA->getCustomEnumFormatterCollection();

        $enumFormatterCollectionB = new EnumFormatterCollection([
            new class extends AbstractEnumFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, object $enum): ?string
                {
                    return null;
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(object $enum): bool
                {
                    return is_enum($enum);
                }
            }
        ]);
        $casterB = $casterA->withCustomEnumFormatterCollection($enumFormatterCollectionB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getCustomEnumFormatterCollection(),
            $casterB->getCustomEnumFormatterCollection(),
        );
        $this->assertSame(
            $enumFormatterCollectionA,
            $casterA->getCustomEnumFormatterCollection(),
        );
        $this->assertCount(
            0,
            $casterA->getCustomEnumFormatterCollection(),
        );
        $this->assertSame(
            $enumFormatterCollectionB,
            $casterB->getCustomEnumFormatterCollection(),
        );
        $this->assertCount(
            1,
            $casterB->getCustomEnumFormatterCollection(),
        );
    }

    public function testWithCustomObjectFormatterCollectionWorks(): void
    {
        $casterA = Caster::create();
        $objectFormatterCollectionA = $casterA->getCustomObjectFormatterCollection();

        $objectFormatterCollectionB = new ObjectFormatterCollection([
            new class extends AbstractObjectFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, object $object): ?string
                {
                    return null;
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(object $object): bool
                {
                    return true;
                }
            }
        ]);
        $casterB = $casterA->withCustomObjectFormatterCollection($objectFormatterCollectionB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getCustomObjectFormatterCollection(),
            $casterB->getCustomObjectFormatterCollection(),
        );
        $this->assertSame(
            $objectFormatterCollectionA,
            $casterA->getCustomObjectFormatterCollection(),
        );
        $this->assertCount(
            0,
            $casterA->getCustomObjectFormatterCollection(),
        );
        $this->assertSame(
            $objectFormatterCollectionB,
            $casterB->getCustomObjectFormatterCollection(),
        );
        $this->assertCount(
            1,
            $casterB->getCustomObjectFormatterCollection(),
        );
    }

    public function testWithCustomResourceFormatterCollectionWorks(): void
    {
        $casterA = Caster::create();
        $resourceFormatterCollectionA = $casterA->getCustomResourceFormatterCollection();

        $resourceFormatterCollectionB = new ResourceFormatterCollection([
            new class extends AbstractResourceFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, Resource_ $resource): ?string
                {
                    return null;
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(Resource_ $resource): bool
                {
                    return true;
                }
            }
        ]);
        $casterB = $casterA->withCustomResourceFormatterCollection($resourceFormatterCollectionB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getCustomResourceFormatterCollection(),
            $casterB->getCustomResourceFormatterCollection(),
        );
        $this->assertSame(
            $resourceFormatterCollectionA,
            $casterA->getCustomResourceFormatterCollection(),
        );
        $this->assertCount(
            0,
            $casterA->getCustomResourceFormatterCollection(),
        );
        $this->assertSame(
            $resourceFormatterCollectionB,
            $casterB->getCustomResourceFormatterCollection(),
        );
        $this->assertCount(
            1,
            $casterB->getCustomResourceFormatterCollection(),
        );
    }

    public function testWithCustomStringFormatterCollectionWorks(): void
    {
        $casterA = Caster::create();
        $stringFormatterCollectionA = $casterA->getCustomStringFormatterCollection();

        $stringFormatterCollectionB = new StringFormatterCollection([
            new class extends AbstractStringFormatter
            {
                /**
                 * {@inheritDoc}
                 */
                public function format(CasterInterface $caster, string $string): ?string
                {
                    return null;
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(string $string): bool
                {
                    return true;
                }
            }
        ]);
        $casterB = $casterA->withCustomStringFormatterCollection($stringFormatterCollectionB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getCustomStringFormatterCollection(),
            $casterB->getCustomStringFormatterCollection(),
        );
        $this->assertSame(
            $stringFormatterCollectionA,
            $casterA->getCustomStringFormatterCollection(),
        );
        $this->assertCount(
            0,
            $casterA->getCustomStringFormatterCollection(),
        );
        $this->assertSame(
            $stringFormatterCollectionB,
            $casterB->getCustomStringFormatterCollection(),
        );
        $this->assertCount(
            1,
            $casterB->getCustomStringFormatterCollection(),
        );
    }

    public function testWithDepthCurrentWorks(): void
    {
        $casterA = Caster::create();
        $depthCurrentA = $casterA->getDepthCurrent();

        $depthCurrentB = new PositiveInteger(1);
        $casterB = $casterA->withDepthCurrent($depthCurrentB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getDepthCurrent(),
            $casterB->getDepthCurrent(),
        );
        $this->assertSame(
            $depthCurrentA,
            $casterA->getDepthCurrent(),
        );
        $this->assertSame(
            $depthCurrentB,
            $casterB->getDepthCurrent(),
        );
    }

    public function testWithDepthMaximumWorks(): void
    {
        $casterA = Caster::create();
        $depthMaximumA = $casterA->getDepthMaximum();

        $depthMaximumB = new PositiveInteger(1);
        $casterB = $casterA->withDepthMaximum($depthMaximumB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getDepthMaximum(),
            $casterB->getDepthMaximum(),
        );
        $this->assertSame(
            $depthMaximumA,
            $casterA->getDepthMaximum(),
        );
        $this->assertSame(
            $depthMaximumB,
            $casterB->getDepthMaximum(),
        );
    }

    public function testWithIsPrependingTypeWorks(): void
    {
        $casterA = Caster::create();

        $casterB = $casterA->withIsPrependingType(true);

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            false,
            $casterA->isPrependingType(),
        );
        $this->assertSame(
            true,
            $casterB->isPrependingType(),
        );
    }

    public function testWithIsMakingSamplesWorks(): void
    {
        $casterA = Caster::create();

        $casterB = $casterA->withIsMakingSamples(false);

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            true,
            $casterA->IsMakingSamples(),
        );
        $this->assertSame(
            false,
            $casterB->IsMakingSamples(),
        );
    }

    public function testWithMaskedEncryptedStringCollectionWorks(): void
    {
        $casterA = Caster::create();
        $maskedEncryptedStringCollectionA = $casterA->getMaskedEncryptedStringCollection();

        $maskedEncryptedStringCollectionB = new EncryptedStringCollection([
            new EncryptedString('foo'),
        ]);
        $casterB = $casterA->withMaskedEncryptedStringCollection(
            $maskedEncryptedStringCollectionB
        );

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            $maskedEncryptedStringCollectionA,
            $casterA->getMaskedEncryptedStringCollection(),
        );
        $this->assertCount(
            0,
            $casterA->getMaskedEncryptedStringCollection(),
        );
        $this->assertSame(
            $maskedEncryptedStringCollectionB,
            $casterB->getMaskedEncryptedStringCollection(),
        );
        $this->assertCount(
            1,
            $casterB->getMaskedEncryptedStringCollection(),
        );
    }

    public function testWithMaskingCharacterWorks(): void
    {
        $casterA = Caster::create();
        $maskingCharacterA = $casterA->getMaskingCharacter();

        $maskingCharacterB = new Character('#');
        $casterB = $casterA->withMaskingCharacter($maskingCharacterB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            $maskingCharacterA,
            $casterA->getMaskingCharacter(),
        );
        $this->assertSame(
            '*',
            (string)$casterA->getMaskingCharacter(),
        );
        $this->assertSame(
            $maskingCharacterB,
            $casterB->getMaskingCharacter(),
        );
        $this->assertSame(
            '#',
            (string)$casterB->getMaskingCharacter(),
        );
    }

    public function testWithMaskingStringLengthWorks(): void
    {
        $casterA = Caster::create();
        $maskingStringLengthA = $casterA->getMaskingStringLength();

        $maskingStringLengthB = new PositiveInteger(10);
        $casterB = $casterA->withMaskingStringLength($maskingStringLengthB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            $maskingStringLengthA,
            $casterA->getMaskingStringLength(),
        );
        $this->assertSame(
            6,
            $casterA->getMaskingStringLength()->toInteger(),
        );
        $this->assertSame(
            $maskingStringLengthB,
            $casterB->getMaskingStringLength(),
        );
        $this->assertSame(
            10,
            $casterB->getMaskingStringLength()->toInteger(),
        );
    }

    public function testWithSampleEllipsisWorks(): void
    {
        $casterA = Caster::create();
        $sampleEllipsisA = $casterA->getSampleEllipsis();

        $sampleEllipsisB = '+++';
        $casterB = $casterA->withSampleEllipsis($sampleEllipsisB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            $sampleEllipsisA,
            $casterA->getSampleEllipsis(),
        );
        $this->assertSame(
            $sampleEllipsisB,
            $casterB->getSampleEllipsis(),
        );
    }

    public function testWithSampleEllipsisThrowsExceptionWhenArgumentSampleEllipsisIsEmpty(): void
    {
        $caster = Caster::create();

        try {
            $caster->withSampleEllipsis('');
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withSampleEllipsis\(',
                            '\$sampleEllipsis = \(string\(0\)\) ""',
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, '/'),
                    preg_quote(Caster::class, '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException)); // Make phpstan happy
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                implode('', [
                    '/',
                    '^',
                    'Argument \$sampleEllipsis is an empty string, which is not allowed',
                    '$',
                    '/',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testWithSampleEllipsisThrowsExceptionWhenWhenArgumentSampleEllipsisWhenTrimmedIsEmpty(): void
    {
        $caster = Caster::create();

        try {
            $caster->withSampleEllipsis('   ');
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withSampleEllipsis\(',
                            '\$sampleEllipsis = \(string\(3\)\) "   "',
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, '/'),
                    preg_quote(Caster::class, '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException)); // Make phpstan happy
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                implode('', [
                    '/',
                    '^',
                    'Argument \$sampleEllipsis contains only white space characters, which is not allowed.',
                    ' Found: \(string\(3\)\) "   "',
                    '$',
                    '/',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testWithSampleEllipsisThrowsExceptionWhenArgumentSampleEllipsisContainsIllegalCharacters(): void
    {
        $caster = Caster::create();

        try {
            $caster->withSampleEllipsis("foo \x0d bar");
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withSampleEllipsis\(',
                            '\$sampleEllipsis = \(string\(9\)\) "foo \r bar"',
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, '/'),
                    preg_quote(Caster::class, '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException)); // Make phpstan happy
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                implode('', [
                    '/',
                    '^',
                    'Argument \$sampleEllipsis contains illegal characters.',
                    ' Found: \(string\(9\)\) "foo \r bar"',
                    '$',
                    '/',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testWithStringSampleSizeWorks(): void
    {
        $casterA = Caster::create();
        $stringSampleSizeA = $casterA->getStringSampleSize();

        $stringSampleSizeB = new UnsignedInteger(1);
        $casterB = $casterA->withStringSampleSize($stringSampleSizeB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            $stringSampleSizeA,
            $casterA->getStringSampleSize(),
        );
        $this->assertSame(
            $stringSampleSizeB,
            $casterB->getStringSampleSize(),
        );
    }

    public function testWithStringQuotingCharacterWorks(): void
    {
        $casterA = Caster::create();

        $casterB = $casterA->withStringQuotingCharacter(new Character('`'));

        $this->assertNotSame($casterA, $casterB);
        $this->assertNotSame(
            $casterA->getStringQuotingCharacter(),
            $casterB->getStringQuotingCharacter(),
        );
        $this->assertSame(
            CasterInterface::STRING_QUOTING_CHARACTER_DEFAULT,
            (string)$casterA->getStringQuotingCharacter(),
        );
        $this->assertSame(
            '`',
            (string)$casterB->getStringQuotingCharacter(),
        );
    }

    public function testWithStringQuotingCharacterThrowsExceptionWhenArgumentStringQuotingCharacterIsBacklash(): void
    {
        $caster = Caster::create();

        try {
            $caster->withStringQuotingCharacter(new Character('\\'));
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withStringQuotingCharacter\(',
                            '\$stringQuotingCharacter = \(object\) \\\\%s \{.+\}', // phpcs:ignore
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, '/'),
                    preg_quote(Character::class, '/'),
                    preg_quote(Caster::class, '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException)); // Make phpstan happy
            $this->assertSame(CasterException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Argument \$stringQuotingCharacter must not be a backslash, but it is\.',
                        ' Found: \(object\) \\\\%s \{.+\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(Character::class, '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testGetInstanceWorks(): void
    {
        $this->assertSame(Caster::getInstance(), Caster::getInstance());
    }

    public function testGetInternalInstanceWorks(): void
    {
        $this->assertSame(Caster::getInternalInstance(), Caster::getInternalInstance());
        $this->assertNotSame(Caster::getInstance(), Caster::getInternalInstance());
    }

    public function testCreateWorks(): void
    {
        $caster = Caster::create();
        $this->assertInstanceOf(Caster::class, $caster);
    }

    /**
     * @dataProvider dataProvider_testMakeNormalizedClassNameWorks
     */
    public function testMakeNormalizedClassNameWorks(string $expectedRegex, object $object): void
    {
        $this->assertMatchesRegularExpression(
            $expectedRegex,
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
        );
    }

    /**
     * @return array<array{string, object}>
     */
    public function dataProvider_testMakeNormalizedClassNameWorks(): array
    {
        return [
            [
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'class@anonymous\/in\/.+\/%s:\d+',
                        '$',
                        '/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class
                {
                },
            ],
            [
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\DateTime@anonymous\/in\/.+\/%s:\d+',
                        '$',
                        '/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class ('2022-01-01T00:00:00+00:00') extends \DateTime
                {
                },
            ],
            [
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        '\\\\%s@anonymous\/in\/.+\/%s:\d+',
                        '$',
                        '/',
                    ]),
                    preg_quote(Character::class, '/'),
                    preg_quote(basename(__FILE__), '/'),
                ),
                new class ('-') extends Character
                {
                },
            ],
        ];
    }

    /**
     * @return ContextInterface&MockObject
     */
    private function mockContextInterface(): ContextInterface
    {
        return $this
            ->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
