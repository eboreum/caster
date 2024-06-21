<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use DateTimeInterface;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\EncryptedString;
use Eboreum\Caster\Exception\RuntimeException;
use Eboreum\Caster\Formatter\Object_\ReflectionParameterFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use SensitiveParameter;
use SplFileObject;
use stdClass;

use function assert;
use function implode;
use function is_object;
use function is_string;
use function preg_quote;
use function sprintf;

use const JSON_ERROR_NONE;

#[CoversClass(ReflectionParameterFormatter::class)]
class ReflectionParameterFormatterTest extends TestCase
{
    private const TEST_CONSTANT_2330CD52C3D911EDAFA10242AC120002 = 0;

    /**
     * @return array<array{string, string, Caster, ReflectionParameterFormatter, ReflectionParameter}>
     */
    public static function providerTestFormatWorksForFunctionParameters(): array
    {
        return [
            [
                'Simple string parameter. No default value.',
                '/^\\\\ReflectionParameter \(string \$haystack\)$/',
                Caster::create(),
                new ReflectionParameterFormatter(),
                new ReflectionParameter('strpos', 'haystack'),
            ],
            [
                'Simple int parameter with a default value being a literal.',
                '/^\\\\ReflectionParameter \(int \$offset = 0\)$/',
                Caster::create(),
                new ReflectionParameterFormatter(),
                new ReflectionParameter('strpos', 'offset'),
            ],
            [
                'Simple int parameter with a default value being a literal and type being prepended.',
                '/^\\\\ReflectionParameter \(int \$offset = \(int\) 0\)$/',
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                new ReflectionParameter('strpos', 'offset'),
            ],
            [
                'Simple int parameter with a default value being a global constant.',
                '/^\\\\ReflectionParameter \(int \$foo = \\\\JSON_ERROR_NONE\)$/',
                Caster::create(),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $function = static function (int $foo = JSON_ERROR_NONE): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    return $reflectionFunction->getParameters()[0];
                })(),
            ],
        ];
    }

    /**
     * @return array<array{string, string, Caster, ReflectionParameterFormatter, ReflectionParameter}>
     */
    public static function providerTestFormatWorksForMethodParameters(): array
    {
        return [
            [
                'Simple int parameter. No default value.',
                '/^\\\\ReflectionParameter \(int \$flags\)$/',
                Caster::create(),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $reflectionMethod = new ReflectionMethod(SplFileObject::class, 'setFlags');

                    return $reflectionMethod->getParameters()[0];
                })(),
            ],
            [
                'Simple int parameter with a default value being a literal.',
                '/^\\\\ReflectionParameter \(int \$length = 0\)$/',
                Caster::create(),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $reflectionMethod = new ReflectionMethod(SplFileObject::class, 'fwrite');

                    return $reflectionMethod->getParameters()[1];
                })(),
            ],
            [
                'Simple int parameter with a default value being a literal and type being prepended.',
                '/^\\\\ReflectionParameter \(int \$length = \(int\) 0\)$/',
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $reflectionMethod = new ReflectionMethod(SplFileObject::class, 'fwrite');

                    return $reflectionMethod->getParameters()[1];
                })(),
            ],
            [
                'Simple int parameter with a default value being a global constant.',
                '/^\\\\ReflectionParameter \(int \$foo = \\\\JSON_ERROR_NONE\)$/',
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $reflectionMethod = new ReflectionMethod(
                        self::class,
                        'resourceMethodForTestFormatWorksForMethodParameters18525b4ec3d911edafa10242ac120002',
                    );

                    return $reflectionMethod->getParameters()[0];
                })(),
            ],
            [
                'Simple int parameter with a default value being a "self::" constant.',
                '/^\\\\ReflectionParameter \(int \$foo = self::TEST_CONSTANT_2330CD52C3D911EDAFA10242AC120002\)$/',
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $reflectionMethod = new ReflectionMethod(
                        self::class,
                        'resourceMethodForTestFormatWorksForMethodParameters2330cd52c3d911edafa10242ac120002',
                    );

                    return $reflectionMethod->getParameters()[0];
                })(),
            ],
            [
                'Simple string parameter with a default value being a "parent::" constant.',
                '/^\\\\ReflectionParameter \(string \$foo = parent::ENCRYPTION_METHOD_DEFAULT\)$/',
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $object = new class ('foo') extends EncryptedString
                    {
                        public function lorem(string $foo = parent::ENCRYPTION_METHOD_DEFAULT): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);
                    $reflectionMethod = $reflectionObject->getMethod('lorem');

                    return $reflectionMethod->getParameters()[0];
                })(),
            ],
            [
                'Simple string parameter with a default value being a class-reference constant.',
                '/^\\\\ReflectionParameter \(string \$foo = \\\\DateTimeInterface::ATOM\)$/',
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $object = new class ('foo') extends EncryptedString
                    {
                        public function lorem(string $foo = DateTimeInterface::ATOM): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);
                    $reflectionMethod = $reflectionObject->getMethod('lorem');

                    return $reflectionMethod->getParameters()[0];
                })(),
            ],
            [
                'Variadic string parameter without default value.',
                '/^\\\\ReflectionParameter \(string \.\.\.\$foo\)$/',
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $object = new class
                    {
                        public function lorem(string ...$foo): void
                        {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);
                    $reflectionMethod = $reflectionObject->getMethod('lorem');

                    return $reflectionMethod->getParameters()[0];
                })(),
            ],
            [
                'Default value and #[\SensitiveParameter]',
                sprintf(
                    '/^\\\\ReflectionParameter \(string \$foo = %s\)$/',
                    preg_quote(CasterInterface::SENSITIVE_MESSAGE_DEFAULT, '/'),
                ),
                Caster::create()->withIsPrependingType(true),
                new ReflectionParameterFormatter(),
                (static function (): ReflectionParameter {
                    $object = new class
                    {
                        public function lorem(
                            #[SensitiveParameter]
                            string $foo = 'bar'
                        ): void {
                        }
                    };

                    $reflectionObject = new ReflectionObject($object);
                    $reflectionMethod = $reflectionObject->getMethod('lorem');

                    return $reflectionMethod->getParameters()[0];
                })(),
            ],
        ];
    }

    public function testFormatWorksWithNonReflectionParameter(): void
    {
        $caster = Caster::create();
        $reflectionParameterFormatter = new ReflectionParameterFormatter();
        $object = new stdClass();

        $this->assertFalse($reflectionParameterFormatter->isHandling($object));
        $this->assertNull($reflectionParameterFormatter->format($caster, $object));
    }

    #[DataProvider('providerTestFormatWorksForFunctionParameters')]
    public function testFormatWorksForFunctionParameters(
        string $message,
        string $expectedRegex,
        Caster $caster,
        ReflectionParameterFormatter $reflectionParameterFormatter,
        ReflectionParameter $reflectionParameter,
    ): void {
        $this->assertTrue($reflectionParameterFormatter->isHandling($reflectionParameter), $message);

        $formatted = $reflectionParameterFormatter->format($caster, $reflectionParameter);

        $this->assertIsString($formatted);
        assert(is_string($formatted));
        $this->assertMatchesRegularExpression($expectedRegex, $formatted, $message);
    }

    #[DataProvider('providerTestFormatWorksForMethodParameters')]
    public function testFormatWorksForMethodParameters(
        string $message,
        string $expectedRegex,
        Caster $caster,
        ReflectionParameterFormatter $reflectionParameterFormatter,
        ReflectionParameter $reflectionParameter,
    ): void {
        $this->assertTrue($reflectionParameterFormatter->isHandling($reflectionParameter), $message);

        $formatted = $reflectionParameterFormatter->format($caster, $reflectionParameter);

        $this->assertIsString($formatted);
        assert(is_string($formatted));
        $this->assertMatchesRegularExpression($expectedRegex, $formatted, $message);
    }

    public function testFormatDefaultValueThrowsExceptionWhenNoDefaultValueIsAvailableOnFunction(): void
    {
        $caster = Caster::create();
        $reflectionParameterFormatter = new ReflectionParameterFormatter();
        $reflectionParameter = new ReflectionParameter('strpos', 'haystack');

        try {
            $reflectionParameterFormatter->formatDefaultValue($caster, $reflectionParameter);
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                implode('', [
                    'A problem was encountered for argument $reflectionParameter, having the parameter name',
                    ' $haystack in function \\strpos',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException));
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                'Parameter $haystack does not have a default value',
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testFormatDefaultValueThrowsExceptionWhenNoDefaultValueIsAvailableOnNonStaticMethod(): void
    {
        $caster = Caster::create();
        $reflectionParameterFormatter = new ReflectionParameterFormatter();
        $reflectionMethod = new ReflectionMethod(SplFileObject::class, 'setFlags');
        $reflectionParameter = $reflectionMethod->getParameters()[0] ?? null;

        $this->assertIsObject($reflectionParameter);
        assert(is_object($reflectionParameter));

        try {
            $reflectionParameterFormatter->formatDefaultValue($caster, $reflectionParameter);
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                implode('', [
                    'A problem was encountered for argument $reflectionParameter, having the parameter name',
                    ' $flags in method \\SplFileObject->setFlags',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException));
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                'Parameter $flags does not have a default value',
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testFormatDefaultValueThrowsExceptionWhenNoDefaultValueIsAvailableOnStaticMethod(): void
    {
        $caster = Caster::create();
        $reflectionParameterFormatter = new ReflectionParameterFormatter();
        $reflectionMethod = new ReflectionMethod(EncryptedString::class, 'isEncryptionMethodValid');
        $reflectionParameter = $reflectionMethod->getParameters()[0] ?? null;

        $this->assertIsObject($reflectionParameter);
        assert(is_object($reflectionParameter));

        try {
            $reflectionParameterFormatter->formatDefaultValue($caster, $reflectionParameter);
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                sprintf(
                    implode('', [
                        'A problem was encountered for argument $reflectionParameter, having the parameter name',
                        ' $encryptionMethod in method \\%s::isEncryptionMethodValid',
                    ]),
                    EncryptedString::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException));
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                'Parameter $encryptionMethod does not have a default value',
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testFormatDefaultValueThrowsExceptionWhenResultingMatchIsInvalid(): void
    {
        $caster = Caster::create();
        $reflectionParameterFormatter = new ReflectionParameterFormatter();
        $reflectionParameter = $this->mockReflectionParameter();

        $reflectionParameter
            ->expects($this->once())
            ->method('isDefaultValueAvailable')
            ->with()
            ->willReturn(true);

        $reflectionParameter
            ->expects($this->once())
            ->method('isDefaultValueConstant')
            ->with()
            ->willReturn(true);

        $reflectionParameter
            ->expects($this->once())
            ->method('getDefaultValueConstantName')
            ->with()
            ->willReturn('');

        $reflectionParameter
            ->expects($this->any())
            ->method('getName')
            ->with()
            ->willReturn('foo');

        try {
            $reflectionParameterFormatter->formatDefaultValue($caster, $reflectionParameter);
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                implode('', [
                    'A problem was encountered for argument $reflectionParameter, having the parameter name $foo in',
                    ' function \\',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException));
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                implode('', [
                    '/^Expects default value of parameter \$foo - a constant - to match regular expression \'.+\', but',
                    ' it does not\. Found: \(string\(0\)\) ""$/',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testWithIsRenderingTypesWorks(): void
    {
        $reflectionParameterFormatterA = new ReflectionParameterFormatter();
        $reflectionParameterFormatterB = $reflectionParameterFormatterA->withIsRenderingTypes(false);

        $this->assertNotSame($reflectionParameterFormatterA, $reflectionParameterFormatterB);
        $this->assertTrue($reflectionParameterFormatterA->isRenderingTypes());
        $this->assertFalse($reflectionParameterFormatterB->isRenderingTypes());
    }

    public function testWithIsWrappingInClassNameWorks(): void
    {
        $reflectionParameterFormatterA = new ReflectionParameterFormatter();
        $reflectionParameterFormatterB = $reflectionParameterFormatterA->withIsWrappingInClassName(false);

        $this->assertNotSame($reflectionParameterFormatterA, $reflectionParameterFormatterB);
        $this->assertTrue($reflectionParameterFormatterA->isWrappingInClassName());
        $this->assertFalse($reflectionParameterFormatterB->isWrappingInClassName());
    }

    public function testWithReflectionTypeFormatterWorks(): void
    {
        $reflectionParameterFormatterA = new ReflectionParameterFormatter();
        $reflectionTypeFormatterA = $reflectionParameterFormatterA->getReflectionTypeFormatter();
        $reflectionTypeFormatterB = $this->mockReflectionTypeFormatter();
        $reflectionParameterFormatterB = $reflectionParameterFormatterA
            ->withReflectionTypeFormatter($reflectionTypeFormatterB);

        $this->assertNotSame($reflectionParameterFormatterA, $reflectionParameterFormatterB);
        $this->assertNotSame(
            $reflectionParameterFormatterA->getReflectionTypeFormatter(),
            $reflectionParameterFormatterB->getReflectionTypeFormatter(),
        );
        $this->assertSame(
            $reflectionTypeFormatterA,
            $reflectionParameterFormatterA->getReflectionTypeFormatter(),
        );
        $this->assertSame(
            $reflectionTypeFormatterB,
            $reflectionParameterFormatterB->getReflectionTypeFormatter(),
        );
    }

    private function resourceMethodForTestFormatWorksForMethodParameters18525b4ec3d911edafa10242ac120002( // @phpstan-ignore-line
        int $foo = JSON_ERROR_NONE,
    ): int {
        return $foo;
    }

    private function resourceMethodForTestFormatWorksForMethodParameters2330cd52c3d911edafa10242ac120002( // @phpstan-ignore-line
        int $foo = self::TEST_CONSTANT_2330CD52C3D911EDAFA10242AC120002,
    ): int {
        return $foo;
    }

    private function mockReflectionParameter(): ReflectionParameter&MockObject
    {
        return $this
            ->getMockBuilder(ReflectionParameter::class)
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
