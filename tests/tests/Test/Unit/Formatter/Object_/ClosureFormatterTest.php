<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use ArrayIterator;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\ClosureFormatter;
use Iterator;
use PHPUnit\Framework\TestCase;
use Traversable;

class ClosureFormatterTest extends TestCase
{
    public const A_CONSTANT = 'foo';

    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $closureFormatter = new ClosureFormatter();
        $object = new \stdClass();

        $this->assertFalse($closureFormatter->isHandling($object));
        $this->assertNull($closureFormatter->format($caster, $object));
    }

    /**
     * @dataProvider dataProvider_testFormatWorks
     */
    public function testFormatWorks(string $message, string $expected, \Closure $closure): void
    {
        $caster = Caster::create();
        $closureFormatter = new ClosureFormatter();

        $this->assertTrue($closureFormatter->isHandling($closure), $message);
        $formatted = $closureFormatter->format($caster, $closure);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertSame($expected, $formatted, $message);
    }

    /**
     * @return array<int, array{string, string, \Closure}>
     */
    public function dataProvider_testFormatWorks(): array
    {
        return [
            [
                '\Closure with no arguments and no return type.',
                '\\Closure()',
                static function () {},
            ],
            [
                '\Closure with 1 argument. No default value. No return type.',
                '\\Closure(int $a)',
                static function (int $a) {},
            ],
            [
                '\Closure with 1 argument. With default value. No return type.',
                '\\Closure(int $a = 42)',
                static function (int $a = 42) {},
            ],
            [
                '\Closure with 1 argument. With default value being a global constant. No return type.',
                '\\Closure(int $a = PHP_INT_MAX)',
                static function (int $a = \PHP_INT_MAX) {},
            ],
            [
                implode('', [
                    '\Closure with 1 argument. With default value being a constant with a `self` reference. No return',
                    ' type.',
                ]),
                '\\Closure(int $a = self::A_CONSTANT)',
                static function (int $a = self::A_CONSTANT) {},
            ],
            [
                implode('', [
                    '\Closure with 1 argument. With default value being a constant with a class name reference. No',
                    ' return type.',
                ]),
                sprintf(
                    '\\Closure(int $a = \\%s::A_CONSTANT)',
                    ClosureFormatterTest::class,
                ),
                static function (int $a = ClosureFormatterTest::A_CONSTANT) {},
            ],
            [
                '\Closure with 3 arguments. No default values. No return type.',
                '\\Closure(int $a, string $b, bool $c)',
                static function (int $a, string $b, bool $c) {},
            ],
            [
                '\Closure with 3 arguments. With 3 default values. No return type.',
                '\\Closure(int $a = 42, string $b = "foo", bool $c = true)',
                static function (int $a = 42, string $b = 'foo', bool $c = true) {},
            ],
            [
                '\Closure with 1 typed variadic argument. No return type.',
                '\\Closure(int ...$a)',
                static function (int ...$a) {},
            ],
            [
                '\Closure with 1 typed variadic argument being nullable. No return type.',
                '\\Closure(?int ...$a)',
                static function (?int ...$a) {},
            ],
            [
                '\Closure with 1 typed argument passed by reference. No return type.',
                '\\Closure(int &$a)',
                static function (int &$a) {},
            ],
            [
                '\Closure with 1 typed argument passed by reference being nullable. No return type.',
                '\\Closure(?int &$a)',
                static function (?int &$a) {},
            ],
            [
                '\Closure with no arguments Return type "int".',
                '\\Closure(): int',
                static function (): int {
                    return 1; // phpstan love
                },
            ],
            [
                '\Closure with no arguments Return type "static".',
                '\\Closure(): static',
                function (): static {
                    return $this; // phpstan love
                },
            ],
            [
                '\Closure with no arguments Return type "?int".',
                '\\Closure(): ?int',
                static function (): ?int {
                    return rand(0, 1) === 1 ? 1 : null; // phpstan love
                },
            ],
            [
                '\Closure with no arguments Return type "int|null" (union). Must get shorted to "?int".',
                '\\Closure(): ?int',
                static function (): int|null {
                    return rand(0, 1) === 1 ? 1 : null; // phpstan love
                },
            ],
            [
                '\Closure with no arguments Return type "int|float|string". Must get normalized to "string|int|float".',
                '\\Closure(): string|int|float',
                static function (): int|float|string {
                    // phpstan love

                    switch (rand(0, 2)) {
                        case 0:
                            return 3.14;
                        case 1:
                            return 42;
                    }

                    return 'foo';
                },
            ],
            [
                '\Closure with no arguments Return type "Traversable&Iterator" (intersection).',
                '\\Closure(): Traversable&Iterator',
                static function (): Traversable&Iterator {
                    return new ArrayIterator([]); // phpstan love
                },
            ],
            [
                'The big one.',
                '\\Closure($a, &$b, int $c, bool $d, \\stdClass $e, array $f = [0 => "lala"], ?string ...$z): int',
                static function ($a, &$b, int $c, bool $d, \stdClass $e, array $f = ['lala'], ?string ...$z): int {
                    return 1; // phpstan love
                },
            ],
        ];
    }
}
