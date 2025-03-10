<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use ArrayIterator;
use Closure;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Formatter\Object_\ClosureFormatter;
use Iterator;
use IteratorIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

use function implode;
use function rand;
use function sprintf;

#[CoversClass(ClosureFormatter::class)]
class ClosureFormatterTest extends TestCase
{
    public const A_CONSTANT = 'foo';

    /**
     * @return array<int, array{string, string, Closure}>
     */
    public static function providerTestFormatWorks(): array
    {
        $caster = Caster::create();

        return [
            [
                '\Closure with no arguments and no return type.',
                '\\Closure()',
                static function () {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 1 argument. No default value. No return type.',
                '\\Closure(int $a)',
                static function (int $a) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 1 argument. With default value. No return type.',
                '\\Closure(int $a = 42)',
                static function (int $a = 42) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 1 argument. With default value being a global constant. No return type.',
                '\\Closure(int $a = PHP_INT_MAX)',
                static function (int $a = \PHP_INT_MAX) {}, // phpcs:ignore
                $caster,
            ],
            [
                implode('', [
                    '\Closure with 1 argument. With default value being a constant with a `self` reference. No return',
                    ' type.',
                ]),
                '\\Closure(int $a = self::A_CONSTANT)',
                // @phpstan-ignore-next-line
                static function (int $a = self::A_CONSTANT) {}, // phpcs:ignore
                $caster,
            ],
            [
                implode('', [
                    '\Closure with 1 argument. With default value being a constant with a class name reference. No',
                    ' return type.',
                ]),
                sprintf(
                    '\\Closure(int $a = \\%s::A_CONSTANT)',
                    self::class,
                ),
                // @phpstan-ignore-next-line
                static function (int $a = ClosureFormatterTest::A_CONSTANT) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 3 arguments. No default values. No return type.',
                '\\Closure(int $a, string $b, bool $c)',
                static function (int $a, string $b, bool $c) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 3 arguments. With 3 default values. No return type.',
                '\\Closure(int $a = 42, string $b = "foo", bool $c = true)',
                static function (int $a = 42, string $b = 'foo', bool $c = true) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 1 typed variadic argument. No return type.',
                '\\Closure(int ...$a)',
                static function (int ...$a) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 1 typed variadic argument being nullable. No return type.',
                '\\Closure(?int ...$a)',
                static function (?int ...$a) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 1 typed argument passed by reference. No return type.',
                '\\Closure(int &$a)',
                static function (int &$a) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with 1 typed argument passed by reference being nullable. No return type.',
                '\\Closure(?int &$a)',
                static function (?int &$a) {}, // phpcs:ignore
                $caster,
            ],
            [
                '\Closure with no arguments Return type "int".',
                '\\Closure(): int',
                static function (): int {
                    return 1; // phpstan love
                },
                $caster,
            ],
            [
                '\Closure with no arguments Return type "static".',
                '\\Closure(): static',
                static function (): static {
                    return null; // @phpstan-ignore-line
                },
                $caster,
            ],
            [
                '\Closure with no arguments Return type "?int".',
                '\\Closure(): ?int',
                static function (): ?int {
                    return rand(0, 1) === 1 ? 1 : null; // phpstan love
                },
                $caster,
            ],
            [
                '\Closure with no arguments Return type "int|null" (union). Must get shorted to "?int".',
                '\\Closure(): ?int',
                static function (): int|null {
                    return rand(0, 1) === 1 ? 1 : null; // phpstan love
                },
                $caster,
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
                $caster,
            ],
            [
                '\Closure with no arguments Return type "Traversable&Iterator" (intersection).',
                '\\Closure(): Traversable&Iterator',
                static function (): Traversable&Iterator {
                    return new ArrayIterator([]); // phpstan love
                },
                $caster,
            ],
            [
                'Parameter union type.',
                '\\Closure(int|float $a)',
                static function (int|float $a) {}, // phpcs:ignore
                $caster,
            ],
            [
                'Return union type.',
                '\\Closure(): int|float',
                static function (): int|float {
                    return rand(0, 1) === 1 ? 42 : 3.14; // phpstan love
                },
                $caster,
            ],
            [
                'Parameter intersection type.',
                '\\Closure(Iterator&Traversable $a)',
                static function (Iterator&Traversable $a) {}, // phpcs:ignore
                $caster,
            ],
            [
                'Return intersection type.',
                '\\Closure(): Iterator&Traversable',
                static function (): Iterator&Traversable {
                    return new IteratorIterator(new ArrayIterator([]));
                },
                $caster,
            ],
            [
                'The big one, without wrapping.',
                '\\Closure($a, &$b, int $c, bool $d, stdClass $e, array $f = [0 => "lala"], ?string ...$z): int',
                static function ($a, &$b, int $c, bool $d, stdClass $e, array $f = ['lala'], ?string ...$z): int {
                    return 1; // phpstan love
                },
                $caster,
            ],
            [
                'The big one, with wrapping.',
                implode("\n", [
                    '\\Closure(',
                    '    $a,',
                    '    &$b,',
                    '    int $c,',
                    '    bool $d,',
                    '    stdClass $e,',
                    '    array $f = [',
                    '        0 => "lala"',
                    '    ],',
                    '    ?string ...$z',
                    '): int',
                ]),
                static function ($a, &$b, int $c, bool $d, stdClass $e, array $f = ['lala'], ?string ...$z): int {
                    return 1; // phpstan love
                },
                $caster->withIsWrapping(true)->withDepthCurrent(new PositiveInteger(2)),
            ],
        ];
    }

    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $closureFormatter = new ClosureFormatter();
        $object = new stdClass();

        $this->assertFalse($closureFormatter->isHandling($object));
        $this->assertNull($closureFormatter->format($caster, $object));
    }

    #[DataProvider('providerTestFormatWorks')]
    public function testFormatWorks(
        string $message,
        string $expected,
        Closure $closure,
        Caster $caster,
    ): void {
        $closureFormatter = new ClosureFormatter();

        $this->assertTrue($closureFormatter->isHandling($closure), $message);
        $formatted = $closureFormatter->format($caster, $closure);
        $this->assertIsString($formatted);
        $this->assertSame($expected, $formatted, $message);
    }
}
