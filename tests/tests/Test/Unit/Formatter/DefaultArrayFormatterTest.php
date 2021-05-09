<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Test\Unit\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Formatter\DefaultArrayFormatter;
use PHPUnit\Framework\TestCase;

class DefaultArrayFormatterTest extends TestCase
{
    /**
     * @dataProvider dataProvier_testBasics
     */
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        Caster $caster,
        array $array
    ): void
    {
        $defaultArrayFormatter = new DefaultArrayFormatter;

        $this->assertTrue($defaultArrayFormatter->isHandling($array), $message);

        $this->assertMatchesRegularExpression(
            $expected,
            $defaultArrayFormatter->format($caster, $array),
            $message,
        );

        $caster = $caster->withIsPrependingType(true);

        $this->assertMatchesRegularExpression(
            $expectedWithType,
            $defaultArrayFormatter->format($caster, $array),
            $message,
        );
    }

    public function dataProvier_testBasics(): array
    {
        return [
            [
                "An empty array",
                '/^\[\]$/',
                '/^\[\]$/',
                Caster::getInstance(),
                [],
            ],
            [
                "A one-dimensional array",
                '/^\[0 =\> "foo"\]$/',
                '/^\[\(int\) 0 =\> \(string\(3\)\) "foo"\]$/',
                Caster::getInstance(),
                [
                    "foo",
                ],
            ],
            [
                "A multidimensional array, not being restricted",
                '/^\["foo" =\> \["bar" =\> \["baz" =\> \[\]\]\]\]$/',
                implode("", [
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
                    "foo" => [
                        "bar" => [
                            "baz" => [],
                        ],
                    ],
                ],
            ],
            [
                "A multidimensional array, being restricted by maximum depth",
                '/^\["foo" =\> \["bar" =\> \[\.\.\.\]\]\]$/',
                implode("", [
                    '/',
                    '^',
                    '\[',
                        '\(string\(3\)\) "foo" =\> \(array\(1\)\) \[',
                            '\(string\(3\)\) "bar" =\> \(array\(1\)\) \[\.\.\.\]',
                        '\]',
                    '\]',
                    '$',
                    '/',
                ]),
                (function(){
                    $caster = Caster::getInstance();
                    $caster = $caster->withDepthMaximum(new PositiveInteger(1));

                    /**
                     * The depth is off by one because we start in DefaultArrayFormatter->format(...) and not in
                     * Caster->cast(...), which explains why we get two levels deep in the expected regexes.
                     */

                    return $caster;
                })(),
                [
                    "foo" => [
                        "bar" => [
                            "baz" => [],
                        ],
                    ],
                ],
            ],
            [
                "A multidimensional array, being restricted by array sample size with 1 element in surplus",
                '/^\["foo" =\> 1, "bar" =\> 2, \.\.\. and 1 more element\] \(sample\)$/',
                implode("", [
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
                (function(){
                    $caster = Caster::getInstance();
                    $caster = $caster->withArraySampleSize(new UnsignedInteger(2));

                    /**
                     * The depth is off by one because we start in DefaultArrayFormatter->format(...) and not in
                     * Caster->cast(...), which explains why we get two levels deep in the expected regexes.
                     */

                    return $caster;
                })(),
                [
                    "foo" => 1,
                    "bar" => 2,
                    "baz" => 3,
                ],
            ],
            [
                "A multidimensional array, being restricted by array sample size with 3 elements in surplus",
                '/^\["foo" =\> 1, "bar" =\> 2, \.\.\. and 3 more elements\] \(sample\)$/',
                implode("", [
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
                (function(){
                    $caster = Caster::getInstance();
                    $caster = $caster->withArraySampleSize(new UnsignedInteger(2));

                    /**
                     * The depth is off by one because we start in DefaultArrayFormatter->format(...) and not in
                     * Caster->cast(...), which explains why we get two levels deep in the expected regexes.
                     */

                    return $caster;
                })(),
                [
                    "foo" => 1,
                    "bar" => 2,
                    "baz" => 3,
                    "bim" => 4,
                    "bum" => 5,
                ],
            ],
        ];
    }

    public function testFormatWorksWhenArraySampleSizeIsZero(): void
    {
        $caster = Caster::create();
        $caster = $caster->withArraySampleSize(new UnsignedInteger(0));
        $defaultArrayFormatter = new DefaultArrayFormatter;

        $this->assertSame(
            "[...] (sample)",
            $defaultArrayFormatter->format($caster, ["foo", 42]),
        );
    }
}
