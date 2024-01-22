<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\EncryptedString;
use Eboreum\Caster\Formatter\Object_\ReflectionMethodFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionParameterFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionObject;
use stdClass;

use function assert;
use function basename;
use function implode;
use function is_string;
use function preg_quote;
use function sprintf;

use const JSON_ERROR_DEPTH;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Formatter\Object_\ReflectionMethodFormatter
 */
class ReflectionMethodFormatterTest extends TestCase
{
    public function testFormatWorksWithNonReflectionMethod(): void
    {
        $caster = Caster::create();
        $reflectionMethodFormatter = new ReflectionMethodFormatter();
        $object = new stdClass();

        $this->assertFalse($reflectionMethodFormatter->isHandling($object));
        $this->assertNull($reflectionMethodFormatter->format($caster, $object));
    }

    public function testFormatWorksWithReflectionMethodWithoutArguments(): void
    {
        $caster = Caster::create();
        $reflectionMethodFormatter = new ReflectionMethodFormatter();
        $reflectionMethod = new ReflectionMethod(self::class, __FUNCTION__);

        $this->assertTrue($reflectionMethodFormatter->isHandling($reflectionMethod));
        $this->assertSame(
            sprintf(
                '\\ReflectionMethod (\\%s->%s(): void)',
                self::class,
                __FUNCTION__,
            ),
            $reflectionMethodFormatter->format($caster, $reflectionMethod)
        );
        $this->assertSame(
            sprintf(
                '\\ReflectionMethod (\\%s->%s: void)',
                self::class,
                __FUNCTION__,
            ),
            $reflectionMethodFormatter->withIsRenderingParameters(false)->format($caster, $reflectionMethod)
        );
        $this->assertSame(
            sprintf(
                '\\ReflectionMethod (\\%s->%s())',
                self::class,
                __FUNCTION__,
            ),
            $reflectionMethodFormatter->withIsRenderingReturnType(false)->format($caster, $reflectionMethod)
        );
        $this->assertSame(
            sprintf(
                '\\%s->%s(): void',
                self::class,
                __FUNCTION__,
            ),
            $reflectionMethodFormatter->withIsWrappingInClassName(false)->format($caster, $reflectionMethod)
        );
    }

    /**
     * @dataProvider dataProviderTestFormatWorksWithReflectionMethodWithArguments
     */
    public function testFormatWorksWithReflectionMethodWithArguments(
        string $message,
        string $expectedRegex,
        Caster $caster,
        ReflectionMethodFormatter $reflectionMethodFormatter,
        ReflectionMethod $reflectionMethod,
    ): void {
        $this->assertTrue($reflectionMethodFormatter->isHandling($reflectionMethod), $message);

        $formatted = $reflectionMethodFormatter->format($caster, $reflectionMethod);

        $this->assertIsString($formatted);
        assert(is_string($formatted));
        $this->assertMatchesRegularExpression($expectedRegex, $formatted, $message);
    }

    /**
     * @return array<array{string, string, Caster, ReflectionMethodFormatter, ReflectionMethod}>
     */
    public function dataProviderTestFormatWorksWithReflectionMethodWithArguments(): array
    {
        return [
            [
                'A single argument. No default value. No return type.',
                sprintf(
                    '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo\)\)$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        // @phpstan-ignore-next-line
                        public function lorem(int $foo) // phpcs:ignore
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'A single argument. No default value. "void" return type.',
                sprintf(
                    '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo\): void\)$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(int $foo): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                '3 arguments. No default values. "void" return type.',
                sprintf(
                    implode('', [
                        '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo, string \$bar',
                        ', bool \$baz\): void\)$/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(int $foo, string $bar, bool $baz): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'A single argument with a primitive default value without type in front. "void" return type.',
                sprintf(
                    '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo = 42\): void\)$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(int $foo = 42): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'A single argument with a primitive default value with type in front. "void" return type.',
                sprintf(
                    implode('', [
                        '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo = \(int\) 42\):',
                        ' void\)$/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create()->withIsPrependingType(true),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(int $foo = 42): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                '3 arguments with a primitive default values and with type in front. "void" return type.',
                sprintf(
                    implode('', [
                        '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo = \(int\) 42',
                        ', string \$bar = \(string\(4\)\) "lala", bool \$baz = \(bool\) false\): void\)$/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create()->withIsPrependingType(true),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(int $foo = 42, string $bar = 'lala', bool $baz = false): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'A single argument with a global constant default value. "void" return type.',
                sprintf(
                    implode('', [
                        '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo =',
                        ' \\\\JSON_ERROR_DEPTH\): void\)$/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(int $foo = JSON_ERROR_DEPTH): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'A single argument with a self-constant default value. "void" return type.',
                sprintf(
                    implode('', [
                        '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(int \$foo = self::FOO\):',
                        ' void\)$/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public const FOO = 42;

                        public function lorem(int $foo = self::FOO): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'A single argument with a parent-constant default value. "void" return type.',
                sprintf(
                    implode('', [
                        '/^\\\\ReflectionMethod \(\\\\%s@anonymous\/in\/.+\/%s:\d+->lorem\(string \$foo =',
                        ' parent::ENCRYPTION_METHOD_DEFAULT\): void\)$/',
                    ]),
                    preg_quote(EncryptedString::class, '/'),
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class ('foo') extends EncryptedString
                    {
                        public function lorem(string $foo = parent::ENCRYPTION_METHOD_DEFAULT): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'No arguments. "int" return type.',
                sprintf(
                    '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(\): int\)$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(): int
                        {
                            return 42;
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'No arguments. Intersection return type.',
                sprintf(
                    implode('', [
                        '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(\):',
                        ' \\\\%s&\\\\%s\)$/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
                    preg_quote(Caster::class, '/'),
                    preg_quote(CasterInterface::class, '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(): Caster&CasterInterface
                        {
                            return Caster::create();
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'No arguments. Union return type.',
                sprintf(
                    '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(\): float|int|string\)$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(): float|int|string
                        {
                            return 42;
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'No arguments. Nullable return type using question mark.',
                sprintf(
                    '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(\): \?int\)$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(): ?int
                        {
                            return 42;
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
            [
                'No arguments. Nullable return type using "int|null". It gets converted to "?int" notation.',
                sprintf(
                    '/^\\\\ReflectionMethod \(class@anonymous\/in\/.+\/%s:\d+->lorem\(\): \?int\)$/',
                    preg_quote(basename(__FILE__), '/'),
                ),
                Caster::create(),
                new ReflectionMethodFormatter(),
                (static function (): ReflectionMethod {
                    $object = new class
                    {
                        public function lorem(): int|null
                        {
                            return 42;
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);

                    return $reflectionObject->getMethod('lorem');
                })(),
            ],
        ];
    }

    public function testFormatWorksWhenWrapping(): void
    {
        $reflectionMethodFormatter = new ReflectionMethodFormatter();

        $object = new class
        {
            public function foo(int $a, float $b, bool $c): void
            {
            }
        };

        $reflectionMethod = new ReflectionMethod($object, 'foo');

        $this->assertSame(
            sprintf(
                implode("\n", [
                    '\\ReflectionMethod (%s->foo(',
                    '    int $a,',
                    '    float $b,',
                    '    bool $c',
                    '): void)',
                ]),
                Caster::makeNormalizedClassName(new ReflectionObject($object)),
            ),
            $reflectionMethodFormatter->format(Caster::create()->withIsWrapping(true), $reflectionMethod),
        );
    }

    public function testWithIsRenderingParametersWorks(): void
    {
        $reflectionMethodFormatterA = new ReflectionMethodFormatter();
        $reflectionMethodFormatterB = $reflectionMethodFormatterA->withIsRenderingParameters(false);

        $this->assertNotSame($reflectionMethodFormatterA, $reflectionMethodFormatterB);
        $this->assertTrue($reflectionMethodFormatterA->isRenderingParameters());
        $this->assertFalse($reflectionMethodFormatterB->isRenderingParameters());
    }

    public function testWithIsRenderingReturnTypeWorks(): void
    {
        $reflectionMethodFormatterA = new ReflectionMethodFormatter();
        $reflectionMethodFormatterB = $reflectionMethodFormatterA->withIsRenderingReturnType(false);

        $this->assertNotSame($reflectionMethodFormatterA, $reflectionMethodFormatterB);
        $this->assertTrue($reflectionMethodFormatterA->isRenderingReturnType());
        $this->assertFalse($reflectionMethodFormatterB->isRenderingReturnType());
    }

    public function testWithIsWrappingInClassNameWorks(): void
    {
        $reflectionMethodFormatterA = new ReflectionMethodFormatter();
        $reflectionMethodFormatterB = $reflectionMethodFormatterA->withIsWrappingInClassName(false);

        $this->assertNotSame($reflectionMethodFormatterA, $reflectionMethodFormatterB);
        $this->assertTrue($reflectionMethodFormatterA->isWrappingInClassName());
        $this->assertFalse($reflectionMethodFormatterB->isWrappingInClassName());
    }

    public function testWithReflectionParameterFormatterWorks(): void
    {
        $reflectionMethodFormatterA = new ReflectionMethodFormatter();
        $reflectionParameterFormatterA = $reflectionMethodFormatterA->getReflectionParameterFormatter();
        $reflectionParameterFormatterB = $this->mockReflectionParameterFormatter();
        $reflectionMethodFormatterB = $reflectionMethodFormatterA
            ->withReflectionParameterFormatter($reflectionParameterFormatterB);

        $this->assertNotSame($reflectionMethodFormatterA, $reflectionMethodFormatterB);
        $this->assertNotSame(
            $reflectionMethodFormatterA->getReflectionParameterFormatter(),
            $reflectionMethodFormatterB->getReflectionParameterFormatter(),
        );
        $this->assertSame(
            $reflectionParameterFormatterA,
            $reflectionMethodFormatterA->getReflectionParameterFormatter(),
        );
        $this->assertSame(
            $reflectionParameterFormatterB,
            $reflectionMethodFormatterB->getReflectionParameterFormatter(),
        );
    }

    public function testWithReflectionTypeFormatterWorks(): void
    {
        $reflectionMethodFormatterA = new ReflectionMethodFormatter();
        $reflectionTypeFormatterA = $reflectionMethodFormatterA->getReflectionTypeFormatter();
        $reflectionTypeFormatterB = $this->mockReflectionTypeFormatter();
        $reflectionMethodFormatterB = $reflectionMethodFormatterA
            ->withReflectionTypeFormatter($reflectionTypeFormatterB);

        $this->assertNotSame($reflectionMethodFormatterA, $reflectionMethodFormatterB);
        $this->assertNotSame(
            $reflectionMethodFormatterA->getReflectionTypeFormatter(),
            $reflectionMethodFormatterB->getReflectionTypeFormatter(),
        );
        $this->assertSame(
            $reflectionTypeFormatterA,
            $reflectionMethodFormatterA->getReflectionTypeFormatter(),
        );
        $this->assertSame(
            $reflectionTypeFormatterB,
            $reflectionMethodFormatterB->getReflectionTypeFormatter(),
        );
    }

    private function mockReflectionParameterFormatter(): ReflectionParameterFormatter&MockObject
    {
        return $this
            ->getMockBuilder(ReflectionParameterFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockReflectionTypeFormatter(): ReflectionTypeFormatter&MockObject
    {
        return $this
            ->getMockBuilder(ReflectionTypeFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
