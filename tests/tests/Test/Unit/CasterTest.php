<?php

declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractStringFormatter;
use Eboreum\Caster\Annotation\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Caster\Context;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Common\DataType\String_\Character;
use Eboreum\Caster\Contract\Caster\ContextInterface;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Exception\CasterException;
use Eboreum\Caster\Formatter\DefaultArrayFormatter;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use Eboreum\Caster\Formatter\DefaultResourceFormatter;
use Eboreum\Caster\Formatter\DefaultStringFormatter;
use Eboreum\Caster\Formatter\Object_\DateIntervalFormatter;
use Eboreum\Caster\Formatter\Object_\DatePeriodFormatter;
use Eboreum\Caster\Formatter\Object_\DateTimeInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\DirectoryFormatter;
use Eboreum\Caster\Formatter\Object_\PublicVariableFormatter;
use Eboreum\Caster\Formatter\Object_\SplFileInfoFormatter;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\ThrowableFormatter;
use Eboreum\Caster\Formatter\Object_\ZipArchiveFormatter;
use Eboreum\Caster\EncryptedString;
use PHPUnit\Framework\TestCase;

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
        $this->assertCount(0, $caster->getCustomObjectFormatterCollection());
        $this->assertCount(0, $caster->getCustomResourceFormatterCollection());
        $this->assertCount(0, $caster->getCustomStringFormatterCollection());
        $this->assertInstanceOf(DefaultArrayFormatter::class, $caster->getDefaultArrayFormatter());
        $this->assertInstanceOf(DefaultObjectFormatter::class, $caster->getDefaultObjectFormatter());
        $this->assertInstanceOf(DefaultResourceFormatter::class, $caster->getDefaultResourceFormatter());
        $this->assertInstanceOf(defaultStringFormatter::class, $caster->getDefaultStringFormatter());
        $this->assertSame(1, $caster->getDepthCurrent()->toInteger());
        $this->assertSame(
            CasterInterface::DEPTH_MAXIMUM_DEFAULT,
            $caster->getDepthMaximum()->toInteger(),
        );
        $this->assertCount(0, $caster->getMaskedEncryptedStringCollection());
        $this->assertSame(
            "*",
            (string)$caster->getMaskingCharacter(),
        );
        $this->assertSame(
            "******",
            $caster->getMaskingString(),
        );
        $this->assertSame(
            6,
            $caster->getMaskingStringLength()->toInteger(),
        );
        $this->assertMatchesRegularExpression(
            implode("", [
                '/',
                '^',
                '\*\* RECURSION \*\* \(',
                    '\\\\stdClass',
                    ', [0-9a-f]{32}',
                '\)',
                '$',
                '/',
            ]),
            $caster->getRecursionMessage(new \stdClass),
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
     * @param mixed $value
     */
    public function testCastWorks(
        string $message,
        string $expected,
        $value,
        Caster $caster
    ): void
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
                "null",
                '/^null$/',
                null,
                Caster::create(),
            ],
            [
                "bool: true",
                '/^true$/',
                true,
                Caster::create(),
            ],
            [
                "bool: false",
                '/^false$/',
                false,
                Caster::create(),
            ],
            [
                "An integer",
                '/^42$/',
                42,
                Caster::create(),
            ],
            [
                "A float",
                '/^3\.14$/',
                3.14,
                Caster::create(),
            ],
            [
                "A string",
                '/^"foo"$/',
                "foo",
                Caster::create(),
            ],
            [
                "object: \stdClass",
                '/^\\\\stdClass$/',
                new \stdClass,
                Caster::create(),
            ],
            [
                "DateIntervalFormatter",
                implode("", [
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
                (new \DateTimeImmutable("2020-01-01 00:00:00"))->diff(new \DateTimeImmutable("2021-02-03 12:34:56")),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DateIntervalFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DatePeriodFormatter",
                implode("", [
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
                    new \DateTimeImmutable("2020-01-01 00:00:00"),
                    new \DateInterval("P1D"),
                    new \DateTimeImmutable("2021-02-03 12:34:56"),
                ),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DatePeriodFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DateTimeInterfaceFormatter",
                implode("", [
                    '/',
                    '^',
                    '\\\\DateTimeImmutable \("2021-02-03T12:34:56\+00:00"\)',
                    '$',
                    '/',
                ]),
                new \DateTimeImmutable("2021-02-03 12:34:56+00:00"),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DateTimeInterfaceFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DebugIdentifierAnnotationInterfaceFormatter",
                '/^class@anonymous\/in\/.+\/CasterTest\.php:\d+ \{\$foo = \(string\(3\)\) "bar"\}$/',
                new class implements DebugIdentifierAnnotationInterface
                {
                    /**
                     * @DebugIdentifier
                     */
                    private string $foo = "bar";
                },
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DebugIdentifierAnnotationInterfaceFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DirectoryFormatter",
                '/^\\\\Directory \{\$path = ".+"\}$/',
                dir(__DIR__),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DirectoryFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "PublicVariableFormatter",
                implode("", [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/CasterTest\.php:\d+ \{',
                        '\$foo = "aaa"',
                        ', \$bar = 42',
                    '\}',
                    '$',
                    '/',
                ]),
                new class
                {
                    public string $foo = "aaa";

                    public int $bar = 42;

                    protected ?float $baz = null;

                    /**
                     * @var array<mixed>
                     */
                    protected array $bim = [];
                },
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new PublicVariableFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "SplFileInfoFormatter",
                implode("", [
                    '/',
                    '^',
                    '\\\\SplFileObject \(".+"\)',
                    '$',
                    '/',
                ]),
                new \SplFileObject(__FILE__),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new SplFileInfoFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "TextuallyIdentifiableInterfaceFormatter",
                '/^class@anonymous\/in\/.+\/CasterTest\.php\:\d+\: AnonymousClass$/',
                new class implements TextuallyIdentifiableInterface
                {
                    public function toTextualIdentifier(CasterInterface $caster): string
                    {
                        return "AnonymousClass";
                    }
                },
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new TextuallyIdentifiableInterfaceFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "ThrowableFormatter",
                implode("", [
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
                (function(){
                    $c = new \LogicException("c", 2);
                    $b = new \RuntimeException("b", 1, $c);

                    return new \Exception("a", 0, $b);
                })(),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new ThrowableFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "ZipArchiveFormatter",
                implode("", [
                    '/',
                    '^',
                    '\\\\ZipArchive \{',
                        '\$status = 0',
                        ', \$statusSys = 0',
                        ', \$numFiles = 0',
                        ', \$filename = ""',
                        ', \$comment = ""',
                    '\}',
                    '$',
                    '/',
                ]),
                new \ZipArchive,
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new ZipArchiveFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "An array",
                '/^\[0 \=\> "foo", 1 \=\> 42\]$/',
                ["foo", 42],
                Caster::create(),
            ],
            [
                "A resource",
                '/^`xml` Resource id #\d+$/',
                \xml_parser_create("UTF-8"),
                Caster::create(),
            ],
        ];
    }

    public function testCastWorksWithStringSample(): void
    {
        $str = str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1);

        $this->assertSame(
            '"' . str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT-4) . ' ..." (sample)',
            Caster::create()->withIsMakingSamples(true)->cast($str),
        );
    }

    public function testCastWorksWithoutStringSample(): void
    {
        $str = str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1);

        $this->assertSame(
            '"' . str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1) . '"',
            Caster::create()->withIsMakingSamples(false)->cast($str),
        );
    }

    public function testCastWorksWithAnonymousClass(): void
    {
        $class = new class {};

        $this->assertMatchesRegularExpression(
            implode("", [
                '/',
                '^',
                'class@anonymous\/in\/.+\/CasterTest\.php:\d+',
                '$',
                '/',
            ]),
            Caster::create()->cast($class),
        );
    }

    public function testCastWorksWithResource(): void
    {
        $this->assertMatchesRegularExpression(
            '/^`stream` Resource id #\d+$/',
            Caster::create()->cast(fopen(__FILE__, "r+")),
        );
    }

    public function testCastWorksWithArrayAndWithSampling(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withArraySampleSize(new UnsignedInteger(3));
        $caster = $caster->withStringSampleSize(new UnsignedInteger(5));
        $array = [
            "foobar",
            "loremipsum" => "dolorsit",
            1,
            2,
            3,
        ];

        $this->assertSame(
            implode("", [
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
            "foobar",
            "loremipsum" => "dolorsit",
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
    public function testCastWorksWithArrayLargerThanSampleSize(
        string $message,
        string $expected,
        array $array
    ): void
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
                "Singular \"element\"",
                '[0 => "foo", 1 => 42, 2 => null, ... and 1 more element] (sample)',
                ["foo", 42, null, false]
            ],
            [
                "Plural \"elements\"",
                '[0 => 1, 1 => 1, 2 => 1, ... and 97 more elements] (sample)',
                array_fill(0, 100, 1),
            ],
        ];
    }

    public function testCastWorksWithAnAssociativeArray(): void
    {
        $array = ["foo" => 1, "bar" => 2, "baz" => 3, "bim" => 4];

        $this->assertSame(
            '["foo" => 1, "bar" => 2, "baz" => 3, ... and 1 more element] (sample)',
            Caster::create()->withIsMakingSamples(true)->cast($array),
        );
    }

    public function testCastWorksWithAMixedArray(): void
    {
        $array = ["foo", "bar" => 2, "baz", "bim" => 4];

        $this->assertSame(
            '[0 => "foo", "bar" => 2, 1 => "baz", ... and 1 more element] (sample)',
            Caster::create()->withIsMakingSamples(true)->cast($array),
        );
    }

    public function testCastWorksWithMaskedStrings(): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));

        $this->assertSame(
            sprintf(
                '"foo %s baz %s" (masked)',
                "******",
                "******"
            ),
            $caster->cast("foo bar baz bim"),
        );
    }

    public function testCastWorksWithMaskedStringsAndSimplifying(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withStringSampleSize(new UnsignedInteger(10));
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));

        $this->assertSame(
            '"foo ** ..." (sample) (masked)',
            $caster->cast("foo bar baz bim"),
        );
    }

    public function testCastWillCorrectlyMaskArrayKeys(): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $array = ["foo bar baz bim" => "bar"];

        // It's the masked length = 19, not the original length. Don't bleed information about masked string
        $this->assertSame(
            sprintf(
                '["foo %s baz %s" (masked) => "%s" (masked)]',
                "******",
                "******",
                "******",
            ),
            $caster->cast($array),
        );
    }

    /**
     * @dataProvider dataProvider_testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings
     * @param EncryptedStringCollection<int, EncryptedString> $encryptedStringCollection
     */
    public function testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings(
        string $expected,
        string $input,
        EncryptedStringCollection $encryptedStringCollection
    ): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection($encryptedStringCollection);

        $this->assertSame($expected, $caster->cast($input));
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: EncryptedStringCollection}>
     */
    public function dataProvider_testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings()
    {
        return [
            [
                sprintf(
                    '"foo %s baz %s bim" (masked)',
                    "******",
                    "******",
                ),
                'foo bar baz *** bim',
                new EncryptedStringCollection(...[
                    new EncryptedString("***"),
                    new EncryptedString("bar"),
                ]),
            ],
            [
                sprintf(
                    '"foo %s baz %s bim" (masked)',
                    "******",
                    "******",
                ),
                'foo bar baz *** bim',
                new EncryptedStringCollection(...[
                    new EncryptedString("bar"),
                    new EncryptedString("***"),
                ]),
            ],
            [
                sprintf(
                    '"foo %s %s baz bim" (masked)',
                    "******",
                    "******",
                ),
                'foo *** bar baz bim',
                new EncryptedStringCollection(...[
                    new EncryptedString("***"),
                    new EncryptedString("bar"),
                ]),
            ],
            [
                sprintf(
                    '"foo %s %s baz bim" (masked)',
                    "******",
                    "******",
                ),
                'foo *** bar baz bim',
                new EncryptedStringCollection(...[
                    new EncryptedString("bar"),
                    new EncryptedString("***"),
                ]),
            ],
            [
                sprintf(
                    '"foo %s bar" (masked)',
                    "******",
                ),
                'foo ********** bar',
                new EncryptedStringCollection(...[
                    new EncryptedString("***"),
                    new EncryptedString("**********"),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_testCastWorksWithTypePrepended
     * @param mixed $value
     */
    public function testCastWorksWithTypePrepended(
        string $message,
        string $expected,
        $value,
        Caster $caster
    ): void
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
                "null",
                '/^\(null\) null$/',
                null,
                Caster::create(),
            ],
            [
                "bool: true",
                '/^\(bool\) true$/',
                true,
                Caster::create(),
            ],
            [
                "bool: false",
                '/^\(bool\) false$/',
                false,
                Caster::create(),
            ],
            [
                "An integer",
                '/^\(int\) 42$/',
                42,
                Caster::create(),
            ],
            [
                "A float",
                '/^\(float\) 3\.14$/',
                3.14,
                Caster::create(),
            ],
            [
                "A string",
                '/^\(string\(3\)\) "foo"$/',
                "foo",
                Caster::create(),
            ],
            [
                "object: \stdClass",
                '/^\(object\) \\\\stdClass$/',
                new \stdClass,
                Caster::create(),
            ],
            [
                "DateIntervalFormatter",
                implode("", [
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
                (new \DateTimeImmutable("2020-01-01 00:00:00"))->diff(new \DateTimeImmutable("2021-02-03 12:34:56")),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DateIntervalFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DatePeriodFormatter",
                implode("", [
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
                    new \DateTimeImmutable("2020-01-01 00:00:00"),
                    new \DateInterval("P1D"),
                    new \DateTimeImmutable("2021-02-03 12:34:56"),
                ),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DatePeriodFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DateTimeInterfaceFormatter",
                implode("", [
                    '/',
                    '^',
                    '\(object\) \\\\DateTimeImmutable \("2021-02-03T12:34:56\+00:00"\)',
                    '$',
                    '/',
                ]),
                new \DateTimeImmutable("2021-02-03 12:34:56+00:00"),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DateTimeInterfaceFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DebugIdentifierAnnotationInterfaceFormatter",
                '/^\(object\) class@anonymous\/in\/.+\/CasterTest\.php:\d+ \{\$foo = \(string\(3\)\) "bar"\}$/',
                new class implements DebugIdentifierAnnotationInterface
                {
                    /**
                     * @DebugIdentifier
                     */
                    private string $foo = "bar";
                },
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DebugIdentifierAnnotationInterfaceFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "DirectoryFormatter",
                '/^\(object\) \\\\Directory \{\$path = \(string\(\d+\)\) ".+"\}$/',
                dir(__DIR__),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new DirectoryFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "PublicVariableFormatter",
                implode("", [
                    '/',
                    '^',
                    '\(object\) class@anonymous\/in\/.+\/CasterTest\.php:\d+ \{',
                        '\$foo = \(string\(3\)\) "aaa"',
                        ', \$bar = \(int\) 42',
                    '\}',
                    '$',
                    '/',
                ]),
                new class
                {
                    public string $foo = "aaa";

                    public int $bar = 42;

                    protected ?float $baz = null;

                    /**
                     * @var array<mixed>
                     */
                    protected array $bim = [];
                },
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new PublicVariableFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "SplFileInfoFormatter",
                implode("", [
                    '/',
                    '^',
                    '\(object\) \\\\SplFileObject \(".+"\)',
                    '$',
                    '/',
                ]),
                new \SplFileObject(__FILE__),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new SplFileInfoFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "TextuallyIdentifiableInterfaceFormatter",
                '/^\(object\) class@anonymous\/in\/.+\/CasterTest\.php\:\d+\: AnonymousClass$/',
                new class implements TextuallyIdentifiableInterface
                {
                    public function toTextualIdentifier(CasterInterface $caster): string
                    {
                        return "AnonymousClass";
                    }
                },
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new TextuallyIdentifiableInterfaceFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "ThrowableFormatter",
                implode("", [
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
                (function(){
                    $c = new \LogicException("c", 2);
                    $b = new \RuntimeException("b", 1, $c);

                    return new \Exception("a", 0, $b);
                })(),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new ThrowableFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "ZipArchiveFormatter",
                implode("", [
                    '/',
                    '^',
                    '\(object\) \\\\ZipArchive \{',
                        '\$status = \(int\) 0',
                        ', \$statusSys = \(int\) 0',
                        ', \$numFiles = \(int\) 0',
                        ', \$filename = \(string\(\d+\)\) ""',
                        ', \$comment = \(string\(\d+\)\) ""',
                    '\}',
                    '$',
                    '/',
                ]),
                new \ZipArchive,
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withCustomObjectFormatterCollection(
                        new ObjectFormatterCollection(...[
                            new ZipArchiveFormatter,
                        ]),
                    );

                    return $caster;
                })(),
            ],
            [
                "An array",
                implode("", [
                    '/',
                    '^',
                    '\(array\(2\)\) \[',
                        '\(int\) 0 \=\> \(string\(\d+\)\) "foo"',
                        ', \(int\) 1 \=\> \(int\) 42',
                    '\]',
                    '$',
                    '/',
                ]),
                ["foo", 42],
                Caster::create(),
            ],
            [
                "A resource",
                '/^\(resource\) `xml` Resource id #\d+$/',
                \xml_parser_create("UTF-8"),
                Caster::create(),
            ],
        ];
    }

    public function testCastWorksWithCustomFormatters(): void
    {
        $caster = Caster::create();
        $caster = $caster->withCustomArrayFormatterCollection(new ArrayFormatterCollection(...[
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

                    $array["replaceme"] = "replaced";

                    return $caster->getDefaultArrayFormatter()->format($caster, $array);
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(array $array): bool
                {
                    return array_key_exists("replaceme", $array);
                }
            },
        ]));
        $caster = $caster->withCustomObjectFormatterCollection(new ObjectFormatterCollection(...[
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

                    assert($object instanceof \DateTimeInterface);

                    return sprintf(
                        "\\%s (%s)",
                        get_class($object),
                        $object->format("c")
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

                    assert($object instanceof \Throwable);

                    return sprintf(
                        "\\%s {\$code = %s, \$file = %s, \$line = %s, \$message = %s}",
                        get_class($object),
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
        $caster = $caster->withCustomResourceFormatterCollection(new ResourceFormatterCollection(...[
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

                    return "YOLO";
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(Resource_ $resource): bool
                {
                    return ("stream" === get_resource_type($resource->getResource()));
                }
            },
        ]));
        $caster = $caster->withCustomStringFormatterCollection(new StringFormatterCollection(...[
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

                    return $caster->getDefaultStringFormatter()->format($caster, "bar");
                }

                /**
                 * {@inheritDoc}
                 */
                public function isHandling(string $string): bool
                {
                    return ("foo" === $string);
                }
            },
        ]));
        $this->assertSame('[0 => 1]', $caster->cast([1]));
        $this->assertSame('["replaceme" => "replaced"]', $caster->cast(["replaceme" => "original"]));
        $this->assertSame('\\stdClass', $caster->cast(new \stdClass));
        $this->assertSame(
            '\\DateTimeImmutable (2019-01-01T00:00:00+00:00)',
            $caster->cast(new \DateTimeImmutable("2019-01-01T00:00:00+00:00")),
        );
        $this->assertMatchesRegularExpression(
            '/^\\\\RuntimeException \{\$code = 1, \$file = "(.+)", \$line = \d+, \$message = "test"\}$/',
            $caster->cast(new \RuntimeException("test", 1))
        );
        $this->assertMatchesRegularExpression(
            '/^`xml` Resource id #\d+$/',
            $caster->cast(\xml_parser_create("UTF-8")),
        );
        $this->assertSame("YOLO", $caster->cast(\fopen(__FILE__, "r+")));
        $this->assertSame('"baz"', $caster->cast("baz"));
        $this->assertSame('"bar"', $caster->cast("foo"));
    }

    public function testCastWorksWithPrependedTypeAndWithStringSample(): void
    {
        $str = str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1);

        $this->assertSame(
            sprintf(
                '(string(%d)) "%s ..." (sample)',
                (CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1),
                str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT-4)
            ),
            Caster::create()->withIsMakingSamples(true)->withIsPrependingType(true)->cast($str),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithoutStringSample(): void
    {
        $str = str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1);

        $this->assertSame(
            sprintf(
                '(string(%d)) "%s"',
                (CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1),
                str_repeat("a", CasterInterface::STRING_SAMPLE_SIZE_DEFAULT+1)
            ),
            Caster::create()->withIsMakingSamples(false)->withIsPrependingType(true)->cast($str),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithAnonymousClass(): void
    {
        $class = new class {};

        $this->assertMatchesRegularExpression(
            implode("", [
                '/',
                '^',
                '\(object\) class@anonymous\/in\/.+\/CasterTest\.php:\d+',
                '$',
                '/',
            ]),
            Caster::create()->withIsPrependingType(true)->cast($class),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithResource(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\(resource\) `stream` Resource id #\d+$/',
            Caster::create()->withIsPrependingType(true)->cast(fopen(__FILE__, "r+")),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithArrayAndWithSampling(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withArraySampleSize(new UnsignedInteger(3));
        $caster = $caster->withStringSampleSize(new UnsignedInteger(5));
        $array = [
            "foobar",
            "loremipsum" => "dolorsit",
            1,
            2,
            3,
        ];
        $expected = implode("", [
            '(array(5)) [',
                '(int) 0 => (string(6)) "f ..." (sample)',
                ', (string(10)) "l ..." (sample) => (string(8)) "d ..." (sample)',
                ', (int) 1 => (int) 1',
                ', ... and 2 more elements',
            '] (sample)',
        ]);

        $this->assertSame(
            implode("", [
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
            "foobar",
            "loremipsum" => "dolorsit",
            1,
            2,
            3,
        ];

        $this->assertSame(
            implode("", [
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
    ): void
    {
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
                "Singular \"element\"",
                '(array(4)) [(int) 0 => (string(3)) "foo", (int) 1 => (int) 42, (int) 2 => (null) null, ... and 1 more element] (sample)',
                ["foo", 42, null, false],
            ],
            [
                "Plural \"elements\"",
                '(array(100)) [(int) 0 => (int) 1, (int) 1 => (int) 1, (int) 2 => (int) 1, ... and 97 more elements] (sample)',
                array_fill(0, 100, 1),
            ],
        ];
    }

    public function testCastWorksWithPrependedTypeAndWithAnAssociativeArray(): void
    {
        $array = ["foo" => 1, "bar" => 2, "baz" => 3, "bim" => 4];

        $this->assertSame(
            implode("", [
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
        $array = ["foo", "bar" => 2, "baz", "bim" => 4];

        $this->assertSame(
            implode("", [
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
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));

        $this->assertSame(
            sprintf(
                '(string(21)) "foo %s baz %s" (masked)',
                "******",
                "******",
            ),
            $caster->withIsPrependingType(true)->cast("foo bar baz bim"),
        );
    }

    public function testCastWorksWithPrependedTypeAndWithMaskedStringsAndSimplifying(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsMakingSamples(true);
        $caster = $caster->withStringSampleSize(new UnsignedInteger(10));
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));

        $this->assertSame(
            '(string(21)) "foo ** ..." (sample) (masked)',
            $caster->withIsPrependingType(true)->cast("foo bar baz bim"),
        );
    }

    public function testCastWorksWithPrependedTypeAndWillCorrectlyMaskArrayKeys(): void
    {
        $caster = Caster::create();
        $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $array = ["foo bar baz bim" => "bar"];

        // It's the masked length = 19, not the original length. Don't bleed information about masked string
        $this->assertSame(
            sprintf(
                '(array(1)) [(string(21)) "foo %s baz %s" (masked) => (string(6)) "%s" (masked)]',
                "******",
                "******",
                "******",
            ),
            $caster->withIsPrependingType(true)->cast($array),
        );
    }

    public function testCastWorksWhenArrayIsBeingOmitted(): void
    {
        $caster = Caster::create();
        $caster = $caster->withDepthMaximum(new PositiveInteger(2));

        $array = [
            "foo" => [
                "bar" => [
                    "baz" => [],
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
                implode("", [
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
        $object = new \stdClass;

        $context = $this->_mockContextInterface();

        $context->expects($this->exactly(2))->method("hasVisitedObject")->with($object)->willReturn(true); /** @phpstan-ignore-line */

        $caster = $caster->withContext($context);

        $this->assertSame(
            $caster->getRecursionMessage($object),
            $caster->cast($object),
        );

        $this->assertSame(
            sprintf(
                "(object) %s",
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
        $object = new \stdClass;

        $this->assertSame(
            sprintf(
                "\\stdClass: %s",
                $caster->getOmittedMaximumDepthOfXReachedMessage(),
            ),
            $caster->cast($object),
        );

        $this->assertSame(
            sprintf(
                "(object) \\stdClass: %s",
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
    public function testMaskStringWorks(
        string $message,
        string $expected,
        Caster $caster,
        string $str
    ): void
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
                "",
                "",
                Caster::create(),
                "",
            ],
            [
                "",
                "foo bar baz",
                Caster::create(),
                "foo bar baz",
            ],
            [
                "",
                sprintf(
                    "foo %s baz",
                    "******",
                ),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
                        new EncryptedString("bar"),
                    ]));

                    return $caster;
                })(),
                "foo bar baz",
            ],
            [
                "",
                sprintf(
                    "12%s78",
                    "******",
                ),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
                        new EncryptedString("3"),
                        new EncryptedString("34"),
                        new EncryptedString("345"),
                        new EncryptedString("3456"),
                    ]));

                    return $caster;
                })(),
                "12345678",
            ],
            [
                "It works with overlapping masking strings",
                sprintf(
                    "12%s67",
                    "******",
                ),
                (function(){
                    $caster = Caster::create();
                    $caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection(...[
                        new EncryptedString("34"),
                        new EncryptedString("45"),
                    ]));

                    return $caster;
                })(),
                "1234567",
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

        $characterEncodingB = new CharacterEncoding("ISO-8859-1");
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

        $contextB = new Context;
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

        $arrayFormatterCollectionB = new ArrayFormatterCollection(...[
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

    public function testWithCustomObjectFormatterCollectionWorks(): void
    {
        $casterA = Caster::create();
        $objectFormatterCollectionA = $casterA->getCustomObjectFormatterCollection();

        $objectFormatterCollectionB = new ObjectFormatterCollection(...[
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

        $resourceFormatterCollectionB = new ResourceFormatterCollection(...[
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

        $stringFormatterCollectionB = new StringFormatterCollection(...[
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

        $maskedEncryptedStringCollectionB = new EncryptedStringCollection(...[
            new EncryptedString("foo"),
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

        $maskingCharacterB = new Character("#");
        $casterB = $casterA->withMaskingCharacter($maskingCharacterB);

        $this->assertNotSame($casterA, $casterB);
        $this->assertSame(
            $maskingCharacterA,
            $casterA->getMaskingCharacter(),
        );
        $this->assertSame(
            "*",
            (string)$casterA->getMaskingCharacter(),
        );
        $this->assertSame(
            $maskingCharacterB,
            $casterB->getMaskingCharacter(),
        );
        $this->assertSame(
            "#",
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

        $sampleEllipsisB = "+++";
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
            $caster->withSampleEllipsis("");
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withSampleEllipsis\(',
                            '\$sampleEllipsis = \(string\(0\)\) ""',
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, "/"),
                    preg_quote(Caster::class, "/"),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Argument \$sampleEllipsis is an empty string, which is not allowed',
                        '$',
                        '/',
                    ]),
                    preg_quote(Character::class, "/"),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(is_null($currentException));

            return;
        }

        $this->fail("Exception was never thrown.");
    }

    public function testWithSampleEllipsisThrowsExceptionWhenWhenArgumentSampleEllipsisWhenTrimmedIsEmpty(): void
    {
        $caster = Caster::create();

        try {
            $caster->withSampleEllipsis("   ");
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withSampleEllipsis\(',
                            '\$sampleEllipsis = \(string\(3\)\) "   "',
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, "/"),
                    preg_quote(Caster::class, "/"),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                implode("", [
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
            $this->assertTrue(is_null($currentException));

            return;
        }

        $this->fail("Exception was never thrown.");
    }

    public function testWithSampleEllipsisThrowsExceptionWhenArgumentSampleEllipsisContainsIllegalCharacters(): void
    {
        $caster = Caster::create();

        try {
            $caster->withSampleEllipsis("foo \x0d bar");
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withSampleEllipsis\(',
                            '\$sampleEllipsis = \(string\(9\)\) "foo \r bar"',
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, "/"),
                    preg_quote(Caster::class, "/"),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                implode("", [
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
            $this->assertTrue(is_null($currentException));

            return;
        }

        $this->fail("Exception was never thrown.");
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

        $casterB = $casterA->withStringQuotingCharacter(new Character("`"));

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
            "`",
            (string)$casterB->getStringQuotingCharacter(),
        );
    }

    public function testWithStringQuotingCharacterThrowsExceptionWhenArgumentStringQuotingCharacterIsBacklash(): void
    {
        $caster = Caster::create();

        try {
            $caster->withStringQuotingCharacter(new Character("\\"));
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Failure in \\\\%s-\>withStringQuotingCharacter\(',
                            '\$stringQuotingCharacter = \(object\) \\\\%s \{.+\}',
                        '\): \(object\) \\\\%s',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, "/"),
                    preg_quote(Character::class, "/"),
                    preg_quote(Caster::class, "/"),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(CasterException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Argument \$stringQuotingCharacter must not be a backslash, but it is\.',
                        ' Found: \(object\) \\\\%s \{.+\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(Character::class, "/"),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(is_null($currentException));

            return;
        }

        $this->fail("Exception was never thrown.");
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

    public function testMakeNormalizedClassNameWorks(): void
    {
        $caster = Caster::create();
        $this->assertInstanceOf(Caster::class, $caster);
    }

    private function _mockContextInterface(): ContextInterface
    {
        return $this
            ->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
