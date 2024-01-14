<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use DateTimeInterface;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use SplFileInfo;
use SplFileObject;
use stdClass;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithReflectionClassArgumentWhenItContainsAnEnumReference\Enum06bcf69ec18d11edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithReflectionClassArgumentWhenItContainsATraitReference\Trait06bcf914c18d11edafa10242ac120002;

use function assert;
use function is_object;
use function is_string;
use function preg_quote;
use function sprintf;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter
 */
class ReflectionTypeFormatterTest extends TestCase
{
    public function testFormatWorksWithNonReflectionType(): void
    {
        $caster = Caster::create();
        $reflectionTypeFormatter = new ReflectionTypeFormatter();
        $object = new stdClass();

        $this->assertFalse($reflectionTypeFormatter->isHandling($object));
        $this->assertNull($reflectionTypeFormatter->format($caster, $object));
    }

    /**
     * @dataProvider dataProviderTestFormatWorksWithReflectionType
     */
    public function testFormatWorksWithReflectionType(
        string $message,
        string $expectedRegex,
        Caster $caster,
        ReflectionTypeFormatter $reflectionTypeFormatter,
        ReflectionType $reflectionType,
    ): void {
        $this->assertTrue($reflectionTypeFormatter->isHandling($reflectionType), $message);

        $formatted = $reflectionTypeFormatter->format($caster, $reflectionType);

        $this->assertIsString($formatted);
        assert(is_string($formatted));
        $this->assertMatchesRegularExpression($expectedRegex, $formatted, $message);
    }

    /**
     * @return array<array{string, string, Caster, ReflectionTypeFormatter, ReflectionType}>
     */
    public function dataProviderTestFormatWorksWithReflectionType(): array
    {
        return [
            [
                'A primitive type, "int".',
                '/^int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $reflectionParameter = new ReflectionParameter('strpos', 'offset');

                    $reflectionType = $reflectionParameter->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'A primitive type, "string".',
                '/^string$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $reflectionParameter = new ReflectionParameter('strpos', 'haystack');

                    $reflectionType = $reflectionParameter->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'A nullable – using question mark syntax – primitive type.',
                '/^\?int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $function = static function (?int $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $this->assertIsObject($reflectionParameter);
                    assert(is_object($reflectionParameter));

                    $reflectionType = $reflectionParameter->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'A nullable – using "|null" syntax – primitive type. It gets converted to question mark syntax.',
                '/^\?int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $function = static function (int|null $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $this->assertIsObject($reflectionParameter);
                    assert(is_object($reflectionParameter));

                    $reflectionType = $reflectionParameter->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'A nullable – using question mark syntax – class reference.',
                sprintf(
                    '/^\?\\\\%s$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $reflectionProperty = new ReflectionProperty(Caster::class, 'instance');

                    $reflectionType = $reflectionProperty->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'A nullable – using "|null" syntax – class reference. It gets converted to question mark syntax.',
                sprintf(
                    '/^\?\\\\%s$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $function = static function (Caster|null $caster): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $this->assertIsObject($reflectionParameter);
                    assert(is_object($reflectionParameter));

                    $reflectionType = $reflectionParameter->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'An intersection type.',
                '/^\\\\SplFileObject&\\\\SplFileInfo$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $function = static function (SplFileObject&SplFileInfo $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $this->assertIsObject($reflectionParameter);
                    assert(is_object($reflectionParameter));

                    $reflectionType = $reflectionParameter->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'A union type.',
                '/^\\\\SplFileObject\|int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                (function (): ReflectionType {
                    $function = static function (SplFileObject|int $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $this->assertIsObject($reflectionParameter);
                    assert(is_object($reflectionParameter));

                    $reflectionType = $reflectionParameter->getType();

                    $this->assertIsObject($reflectionType);
                    assert(is_object($reflectionType));

                    return $reflectionType;
                })(),
            ],
            [
                'The bottom fallback.',
                '/^foo$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                (static function (): ReflectionType {
                    return new class extends ReflectionType
                    {
                        public function __toString(): string
                        {
                            return 'foo';
                        }

                        public function allowsNull(): bool
                        {
                            return true;
                        }
                    };
                })(),
            ],
        ];
    }

    public function testIsClassishReferenceWorks(): void
    {
        $this->assertFalse(ReflectionTypeFormatter::isClassishReference(''));
        $this->assertFalse(ReflectionTypeFormatter::isClassishReference('int'));
        $this->assertFalse(ReflectionTypeFormatter::isClassishReference('string'));
        $this->assertTrue(ReflectionTypeFormatter::isClassishReference('SplFileObject'));
        $this->assertTrue(ReflectionTypeFormatter::isClassishReference(ReflectionTypeFormatter::class));
        $this->assertTrue(ReflectionTypeFormatter::isClassishReference(Enum06bcf69ec18d11edafa10242ac120002::class));
        $this->assertTrue(ReflectionTypeFormatter::isClassishReference(DateTimeInterface::class));
        $this->assertTrue(ReflectionTypeFormatter::isClassishReference(Trait06bcf914c18d11edafa10242ac120002::class));
    }

    public function testWithIsWrappingInClassNameWorks(): void
    {
        $reflectionTypeFormatterA = new ReflectionTypeFormatter();
        $reflectionTypeFormatterB = $reflectionTypeFormatterA->withIsWrappingInClassName(false);

        $this->assertNotSame($reflectionTypeFormatterA, $reflectionTypeFormatterB);
        $this->assertTrue($reflectionTypeFormatterA->isWrappingInClassName());
        $this->assertFalse($reflectionTypeFormatterB->isWrappingInClassName());
    }
}
