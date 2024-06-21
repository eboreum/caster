<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Closure;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Formatter\DefaultArrayFormatter;
use Eboreum\Caster\Formatter\Object_\ClosureFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function assert;
use function implode;
use function is_string;

#[CoversClass(DefaultArrayFormatter::class)]
class DefaultArrayFormatterTest extends TestCase
{
    /**
     * @return array<int, array{string, string, string, Caster|Caster|Closure(self):Caster, array<mixed>}>
     */
    public static function providerTestBasics(): array
    {
        return [
            [
                'An empty array',
                '/^\[\]$/',
                '/^\[\]$/',
                Caster::getInstance(),
                [],
            ],
            [
                'A one-dimensional array',
                '/^\[0 =\> "foo"\]$/',
                '/^\[\(int\) 0 =\> \(string\(3\)\) "foo"\]$/',
                Caster::getInstance(),
                ['foo'],
            ],
            [
                'A multidimensional array, not being restricted',
                '/^\["foo" =\> \["bar" =\> \["baz" =\> \[\]\]\]\]$/',
                implode('', [
                    '/',
                    '^',
                    '\[',
                        '\(string\(3\)\) "foo" =\> \(array\(1\)\) \[',
                            '\(string\(3\)\) "bar" =\> \(array\(1\)\) \[',
                                '\(string\(3\)\) "baz" =\> \(array\(0\)\) \[\]',
                            '\]',
                        '\]',
                    '\]',
                    '$',
                    '/',
                ]),
                Caster::getInstance(),
                [
                    'foo' => [
                        'bar' => [
                            'baz' => [],
                        ],
                    ],
                ],
            ],
            [
                'A multidimensional array, being restricted by maximum depth',
                '/^\["foo" =\> \["bar" =\> \[\.\.\.\] \*\* OMITTED \*\* \(maximum depth of 1 reached\)\]\]$/',
                implode('', [
                    '/',
                    '^',
                    '\[',
                        '\(string\(3\)\) "foo" =\> \(array\(1\)\) \[',
                            '\(string\(3\)\) "bar" =\> \(array\(1\)\) \[\.\.\.\]',
                            ' \*\* OMITTED \*\* \(maximum depth of 1 reached\)',
                        '\]',
                    '\]',
                    '$',
                    '/',
                ]),
                (static function () {
                    $caster = Caster::getInstance();
                    $caster = $caster->withDepthMaximum(new PositiveInteger(1));

                    /**
                     * The depth is off by one because we start in DefaultArrayFormatter->format(...) and not in
                     * Caster->cast(...), which explains why we get two levels deep in the expected regexes.
                     */

                    return $caster;
                })(),
                [
                    'foo' => [
                        'bar' => [
                            'baz' => [],
                        ],
                    ],
                ],
            ],
            [
                'A multidimensional array, being restricted by array sample size with 1 element in surplus',
                '/^\["foo" =\> 1, "bar" =\> 2, \.\.\. and 1 more element\] \(sample\)$/',
                implode('', [
                    '/',
                    '^',
                    '\[',
                        '\(string\(3\)\) "foo" =\> \(int\) 1',
                        ', \(string\(3\)\) "bar" =\> \(int\) 2',
                        ', \.\.\. and 1 more element',
                    '\] \(sample\)',
                    '$',
                    '/',
                ]),
                (static function () {
                    $caster = Caster::getInstance();
                    $caster = $caster->withArraySampleSize(new UnsignedInteger(2));

                    /**
                     * The depth is off by one because we start in DefaultArrayFormatter->format(...) and not in
                     * Caster->cast(...), which explains why we get two levels deep in the expected regexes.
                     */

                    return $caster;
                })(),
                [
                    'foo' => 1,
                    'bar' => 2,
                    'baz' => 3,
                ],
            ],
            [
                'A multi element array, being restricted by array sample size with 3 elements in surplus',
                '/^\["foo" =\> 1, "bar" =\> 2, \.\.\. and 3 more elements\] \(sample\)$/',
                implode('', [
                    '/',
                    '^',
                    '\[',
                        '\(string\(3\)\) "foo" =\> \(int\) 1',
                        ', \(string\(3\)\) "bar" =\> \(int\) 2',
                        ', \.\.\. and 3 more elements',
                    '\] \(sample\)',
                    '$',
                    '/',
                ]),
                (static function () {
                    $caster = Caster::getInstance();
                    $caster = $caster->withArraySampleSize(new UnsignedInteger(2));

                    /**
                     * The depth is off by one because we start in DefaultArrayFormatter->format(...) and not in
                     * Caster->cast(...), which explains why we get two levels deep in the expected regexes.
                     */

                    return $caster;
                })(),
                [
                    'foo' => 1,
                    'bar' => 2,
                    'baz' => 3,
                    'bim' => 4,
                    'bum' => 5,
                ],
            ],
            [
                'A multidimensional array with wrapping enabled.',
                implode('', [
                    '/',
                    '^',
                    '\[',
                    '\n    "foo" \=\> \[',
                    '\n        "bar" \=\> \[',
                    '\n            "a" => 42',
                    '\n        \],',
                    '\n        "baz" \=\> \[',
                    '\n            "b" => 43',
                    '\n        \]',
                    '\n    \]',
                    '\n\]',
                    '$',
                    '/',
                ]),
                implode('', [
                    '/',
                    '^',
                    '\[',
                    '\n    \(string\(3\)\) "foo" \=\> \(array\(2\)\) \[',
                    '\n        \(string\(3\)\) "bar" \=\> \(array\(1\)\) \[',
                    '\n            \(string\(1\)\) "a" => \(int\) 42',
                    '\n        \],',
                    '\n        \(string\(3\)\) "baz" \=\> \(array\(1\)\) \[',
                    '\n            \(string\(1\)\) "b" => \(int\) 43',
                    '\n        \]',
                    '\n    \]',
                    '\n\]',
                    '$',
                    '/',
                ]),
                Caster::getInstance()->withIsWrapping(true)->withDepthCurrent(new PositiveInteger(2)),
                [
                    'foo' => [
                        'bar' => ['a' => 42],
                        'baz' => ['b' => 43],
                    ],
                ],
            ],
            [
                'An array with a wrappable object.',
                implode('', [
                    '/',
                    '^',
                    '\[',
                    '\n    "foo" \=\> \\\\Closure',
                    '\n\]',
                    '$',
                    '/',
                ]),
                implode('', [
                    '/',
                    '^',
                    '\[',
                    '\n    \(string\(3\)\) "foo" \=\> \(object\) \\\\Closure',
                    '\n\]',
                    '$',
                    '/',
                ]),
                static function (self $self): Caster {
                    $formatter = $self->createMock(ClosureFormatter::class);

                    $formatter
                        ->expects($self->atLeastOnce())
                        ->method('format')
                        ->willReturn('\\Closure');

                    return Caster::getInstance()
                        ->withIsWrapping(true)
                        ->withCustomObjectFormatterCollection(
                            new ObjectFormatterCollection([$formatter]),
                        );
                },
                [
                    'foo' => static function (): int {
                        return 42;
                    },
                ],
            ],
        ];
    }

    /**
     * @param Caster|Closure(self):Caster $caster
     * @param array<string|array<mixed>> $array
     */
    #[DataProvider('providerTestBasics')]
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        Caster|Closure $caster,
        array $array,
    ): void {
        if ($caster instanceof Closure) {
            $caster = $caster($this);
        }

        $defaultArrayFormatter = new DefaultArrayFormatter();

        $this->assertTrue($defaultArrayFormatter->isHandling($array), $message);

        $formatted = $defaultArrayFormatter->format($caster, $array);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression($expected, $formatted, $message);

        $caster = $caster->withIsPrependingType(true);
        $formatted = $defaultArrayFormatter->format($caster, $array);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression($expectedWithType, $formatted, $message);
    }

    public function testFormatWorksWhenArraySampleSizeIsZero(): void
    {
        $caster = Caster::create();
        $caster = $caster->withArraySampleSize(new UnsignedInteger(0));
        $defaultArrayFormatter = new DefaultArrayFormatter();

        $this->assertSame(
            '[...] (sample)',
            $defaultArrayFormatter->format($caster, ['foo', 42]),
        );
    }
}
