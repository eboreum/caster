<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Closure;
use DateTimeInterface;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use SplFileInfo;
use SplFileObject;
use stdClass;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithReflectionClassArgumentWhenItContainsAnEnumReference\Enum06bcf69ec18d11edafa10242ac120002; // phpcs:ignore
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithReflectionClassArgumentWhenItContainsATraitReference\Trait06bcf914c18d11edafa10242ac120002; // phpcs:ignore

use function preg_quote;
use function sprintf;

#[CoversClass(ReflectionTypeFormatter::class)]
class ReflectionTypeFormatterTest extends TestCase
{
    /**
     * @return array<array{string, string, Caster, ReflectionTypeFormatter, Closure(self):ReflectionType}>
     */
    public static function providerTestFormatWorksWithReflectionType(): array
    {
        return [
            [
                'A primitive type, "int".',
                '/^int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $reflectionParameter = new ReflectionParameter('strpos', 'offset');

                    $reflectionType = $reflectionParameter->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'A primitive type, "string".',
                '/^string$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $reflectionParameter = new ReflectionParameter('strpos', 'haystack');

                    $reflectionType = $reflectionParameter->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'A nullable – using question mark syntax – primitive type.',
                '/^\?int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $function = static function (?int $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $self->assertIsObject($reflectionParameter);

                    $reflectionType = $reflectionParameter->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'A nullable – using "|null" syntax – primitive type. It gets converted to question mark syntax.',
                '/^\?int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $function = static function (int|null $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $self->assertIsObject($reflectionParameter);

                    $reflectionType = $reflectionParameter->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'A nullable – using question mark syntax – class reference.',
                sprintf(
                    '/^\?\\\\%s$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $reflectionProperty = new ReflectionProperty(Caster::class, 'instance');

                    $reflectionType = $reflectionProperty->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'A nullable – using "|null" syntax – class reference. It gets converted to question mark syntax.',
                sprintf(
                    '/^\?\\\\%s$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $function = static function (Caster|null $caster): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $self->assertIsObject($reflectionParameter);

                    $reflectionType = $reflectionParameter->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'An intersection type.',
                '/^\\\\SplFileObject&\\\\SplFileInfo$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $function = static function (SplFileObject&SplFileInfo $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $self->assertIsObject($reflectionParameter);

                    $reflectionType = $reflectionParameter->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'A union type.',
                '/^\\\\SplFileObject\|int$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (self $self): ReflectionType {
                    $function = static function (SplFileObject|int $foo): void {
                    };
                    $reflectionFunction = new ReflectionFunction($function);

                    /** @var ReflectionParameter|null $reflectionParameter */
                    $reflectionParameter = $reflectionFunction->getParameters()[0] ?? null;

                    $self->assertIsObject($reflectionParameter);

                    $reflectionType = $reflectionParameter->getType();

                    $self->assertIsObject($reflectionType);

                    return $reflectionType;
                },
            ],
            [
                'The bottom fallback.',
                '/^foo$/',
                Caster::create(),
                new ReflectionTypeFormatter(),
                static function (): ReflectionType {
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
                },
            ],
        ];
    }

    public function testFormatWorksWithNonReflectionType(): void
    {
        $caster = Caster::create();
        $reflectionTypeFormatter = new ReflectionTypeFormatter();
        $object = new stdClass();

        $this->assertFalse($reflectionTypeFormatter->isHandling($object));
        $this->assertNull($reflectionTypeFormatter->format($caster, $object));
    }

    /**
     * @param Closure(self):ReflectionType $reflectionTypeFactory
     */
    #[DataProvider('providerTestFormatWorksWithReflectionType')]
    public function testFormatWorksWithReflectionType(
        string $message,
        string $expectedRegex,
        Caster $caster,
        ReflectionTypeFormatter $reflectionTypeFormatter,
        Closure $reflectionTypeFactory,
    ): void {
        $reflectionType = $reflectionTypeFactory($this);

        $this->assertTrue($reflectionTypeFormatter->isHandling($reflectionType), $message);

        $formatted = $reflectionTypeFormatter->format($caster, $reflectionType);

        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression($expectedRegex, $formatted, $message);
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
