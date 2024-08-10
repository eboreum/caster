<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
// phpcs:disable
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWithIntegerIndexedArguments\Attributeda304392c18711edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWithIntegerIndexedArguments\Classda304392c18711edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWithoutArguments\Attributeda304090c18711edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWithoutArguments\Classda304090c18711edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWitNamedArguments\Attributef982a9e0c18911edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithAReflectionAttributeWitNamedArguments\Classf982a9e0c18911edafa10242ac120002;
// phpcs:enable
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithASensitiveNamedArgument\Attribute5773ba9c73ed11eeb9620242ac120002; // phpcs:ignore

use function assert;
use function implode;
use function is_object;
use function sprintf;

#[CoversClass(ReflectionAttributeFormatter::class)]
class ReflectionAttributeFormatterTest extends TestCase
{
    public function testFormatWorksWithNonReflectionAttributes(): void
    {
        $caster = Caster::create();
        $reflectionAttributeFormatter = new ReflectionAttributeFormatter();
        $object = new stdClass();

        $this->assertFalse($reflectionAttributeFormatter->isHandling($object));
        $this->assertNull($reflectionAttributeFormatter->format($caster, $object));
    }

    public function testFormatWorksWithAReflectionAttributeWithoutArguments(): void
    {
        $caster = Caster::create();
        $reflectionAttributeFormatter = new ReflectionAttributeFormatter();
        $reflectionClass = new ReflectionClass(Classda304090c18711edafa10242ac120002::class);

        /** @var ReflectionAttribute<Attributeda304090c18711edafa10242ac120002>|null $reflectionAttribute */
        $reflectionAttribute = (
            $reflectionClass->getAttributes(Attributeda304090c18711edafa10242ac120002::class)[0] ?? null
        );

        $this->assertIsObject($reflectionAttribute);
        assert(is_object($reflectionAttribute));

        $this->assertTrue($reflectionAttributeFormatter->isHandling($reflectionAttribute));
        $this->assertSame(
            sprintf(
                '\\ReflectionAttribute (\\%s)',
                Attributeda304090c18711edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->format($caster, $reflectionAttribute),
        );
        $this->assertSame(
            sprintf(
                '\\%s',
                Attributeda304090c18711edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->withIsWrappingInClassName(false)->format($caster, $reflectionAttribute),
        );
    }

    public function testFormatWorksWithAReflectionAttributeWithIntegerIndexedArguments(): void
    {
        $caster = Caster::create();
        $reflectionAttributeFormatter = new ReflectionAttributeFormatter();
        $reflectionClass = new ReflectionClass(Classda304392c18711edafa10242ac120002::class);

        /** @var ReflectionAttribute<Attributeda304392c18711edafa10242ac120002>|null $reflectionAttribute */
        $reflectionAttribute = (
            $reflectionClass->getAttributes(Attributeda304392c18711edafa10242ac120002::class)[0] ?? null
        );

        $this->assertIsObject($reflectionAttribute);
        assert(is_object($reflectionAttribute));

        $this->assertTrue($reflectionAttributeFormatter->isHandling($reflectionAttribute));
        $this->assertSame(
            sprintf(
                '\\ReflectionAttribute (\\%s (0: "lorem", 1: "ipsum"))',
                Attributeda304392c18711edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->format($caster, $reflectionAttribute),
        );
        $this->assertSame(
            sprintf(
                implode('', [
                    '\\ReflectionAttribute ((attribute) \\%s (0: (string(5)) "lorem", 1: (string(5))',
                    ' "ipsum"))',
                ]),
                Attributeda304392c18711edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->format($caster->withIsPrependingType(true), $reflectionAttribute),
        );
        $this->assertSame(
            sprintf(
                '\\%s (0: "lorem", 1: "ipsum")',
                Attributeda304392c18711edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->withIsWrappingInClassName(false)->format($caster, $reflectionAttribute),
        );
        $this->assertSame(
            sprintf(
                '(attribute) \\%s (0: (string(5)) "lorem", 1: (string(5)) "ipsum")',
                Attributeda304392c18711edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter
                ->withIsWrappingInClassName(false)
                ->format($caster->withIsPrependingType(true), $reflectionAttribute),
        );
    }

    public function testFormatWorksWithAReflectionAttributeWitNamedArguments(): void
    {
        $caster = Caster::create();
        $reflectionAttributeFormatter = new ReflectionAttributeFormatter();
        $reflectionClass = new ReflectionClass(Classf982a9e0c18911edafa10242ac120002::class);

        /** @var ReflectionAttribute<Attributef982a9e0c18911edafa10242ac120002>|null $reflectionAttribute */
        $reflectionAttribute = (
            $reflectionClass->getAttributes(Attributef982a9e0c18911edafa10242ac120002::class)[0] ?? null
        );

        $this->assertIsObject($reflectionAttribute);
        assert(is_object($reflectionAttribute));

        $this->assertTrue($reflectionAttributeFormatter->isHandling($reflectionAttribute));
        $this->assertSame(
            sprintf(
                '\\ReflectionAttribute (\\%s (foo: "lorem", bar: "ipsum"))',
                Attributef982a9e0c18911edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->format($caster, $reflectionAttribute),
        );
        $this->assertSame(
            sprintf(
                '\\ReflectionAttribute ((attribute) \\%s (foo: (string(5)) "lorem", bar: (string(5)) "ipsum"))',
                Attributef982a9e0c18911edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->format($caster->withIsPrependingType(true), $reflectionAttribute),
        );
        $this->assertSame(
            sprintf(
                '\\%s (foo: "lorem", bar: "ipsum")',
                Attributef982a9e0c18911edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter->withIsWrappingInClassName(false)->format($caster, $reflectionAttribute),
        );
        $this->assertSame(
            sprintf(
                '(attribute) \\%s (foo: (string(5)) "lorem", bar: (string(5)) "ipsum")',
                Attributef982a9e0c18911edafa10242ac120002::class,
            ),
            $reflectionAttributeFormatter
                ->withIsWrappingInClassName(false)
                ->format($caster->withIsPrependingType(true), $reflectionAttribute),
        );
    }

    public function testFormatWorksWithASensitiveNamedArgument(): void
    {
        $caster = Caster::create();
        $caster = $caster->withIsPrependingType(true);
        $reflectionAttributeFormatter = new ReflectionAttributeFormatter();

        $object = new class
        {
            #[Attribute5773ba9c73ed11eeb9620242ac120002(foo: 'bar')]
            private string $lorem; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
        };

        $reflectionProperty = new ReflectionProperty($object, 'lorem');

        /** @var ReflectionAttribute<Attribute5773ba9c73ed11eeb9620242ac120002>|null $reflectionAttribute */
        $reflectionAttribute = (
            $reflectionProperty->getAttributes(Attribute5773ba9c73ed11eeb9620242ac120002::class)[0] ?? null
        );

        $this->assertIsObject($reflectionAttribute);
        assert(is_object($reflectionAttribute));

        $this->assertTrue($reflectionAttributeFormatter->isHandling($reflectionAttribute));
        $this->assertSame(
            sprintf(
                '\\ReflectionAttribute ((attribute) \\%s (foo: %s))',
                Attribute5773ba9c73ed11eeb9620242ac120002::class,
                CasterInterface::SENSITIVE_MESSAGE_DEFAULT,
            ),
            $reflectionAttributeFormatter->format($caster, $reflectionAttribute),
        );
    }

    public function testWithIsWrappingInClassNameWorks(): void
    {
        $reflectionAttributeFormatterA = new ReflectionAttributeFormatter();
        $reflectionAttributeFormatterB = $reflectionAttributeFormatterA->withIsWrappingInClassName(false);

        $this->assertNotSame($reflectionAttributeFormatterA, $reflectionAttributeFormatterB);
        $this->assertTrue($reflectionAttributeFormatterA->isWrappingInClassName());
        $this->assertFalse($reflectionAttributeFormatterB->isWrappingInClassName());
    }
}
