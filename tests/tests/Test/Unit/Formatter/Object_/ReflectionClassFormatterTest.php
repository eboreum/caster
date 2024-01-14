<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\ReflectionClassFormatter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithReflectionClassArgumentWhenItContainsAnEnumReference\Enum06bcf69ec18d11edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithReflectionClassArgumentWhenItContainsAnInterfaceReference\Interface06bcfa5ec18d11edafa10242ac120002;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatterTest\testFormatWorksWithReflectionClassArgumentWhenItContainsATraitReference\Trait06bcf914c18d11edafa10242ac120002;

use function sprintf;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Formatter\Object_\ReflectionClassFormatter
 */
class ReflectionClassFormatterTest extends TestCase
{
    public function testFormatWorksWithNonReflectionClass(): void
    {
        $caster = Caster::create();
        $reflectionClassFormatter = new ReflectionClassFormatter();
        $object = new stdClass();

        $this->assertFalse($reflectionClassFormatter->isHandling($object));
        $this->assertNull($reflectionClassFormatter->format($caster, $object));
    }

    public function testFormatWorksWithReflectionClassArgumentWhenItContainsAClassReference(): void
    {
        $caster = Caster::create();
        $reflectionClassFormatter = new ReflectionClassFormatter();
        $reflectionClass = new ReflectionClass(self::class);

        $this->assertTrue($reflectionClassFormatter->isHandling($reflectionClass));
        $this->assertSame(
            sprintf(
                '\\ReflectionClass (\\%s)',
                self::class,
            ),
            $reflectionClassFormatter->format($caster, $reflectionClass)
        );
        $this->assertSame(
            sprintf(
                '\\ReflectionClass ((class) \\%s)',
                self::class,
            ),
            $reflectionClassFormatter->format($caster->withIsPrependingType(true), $reflectionClass),
        );
        $this->assertSame(
            sprintf(
                '\\%s',
                self::class,
            ),
            $reflectionClassFormatter->withIsWrappingInClassName(false)->format($caster, $reflectionClass),
        );
        $this->assertSame(
            sprintf(
                '(class) \\%s',
                self::class,
            ),
            $reflectionClassFormatter
                ->withIsWrappingInClassName(false)
                ->format(
                    $caster->withIsPrependingType(true),
                    $reflectionClass,
                ),
        );
    }

    public function testFormatWorksWithReflectionClassArgumentWhenItContainsAnEnumReference(): void
    {
        $caster = Caster::create();
        $reflectionClassFormatter = new ReflectionClassFormatter();
        $reflectionClass = new ReflectionClass(Enum06bcf69ec18d11edafa10242ac120002::class);

        $this->assertTrue($reflectionClass->isEnum());
        $this->assertTrue($reflectionClassFormatter->isHandling($reflectionClass));
        $this->assertSame(
            sprintf(
                '\\ReflectionClass (\\%s)',
                Enum06bcf69ec18d11edafa10242ac120002::class,
            ),
            $reflectionClassFormatter->format($caster, $reflectionClass),
        );
        $this->assertSame(
            sprintf(
                '\\ReflectionClass ((enum) \\%s)',
                Enum06bcf69ec18d11edafa10242ac120002::class,
            ),
            $reflectionClassFormatter->format($caster->withIsPrependingType(true), $reflectionClass),
        );
    }

    public function testFormatWorksWithReflectionClassArgumentWhenItContainsAnInterfaceReference(): void
    {
        $caster = Caster::create();
        $reflectionClassFormatter = new ReflectionClassFormatter();
        $reflectionClass = new ReflectionClass(Interface06bcfa5ec18d11edafa10242ac120002::class);

        $this->assertTrue($reflectionClass->isInterface());
        $this->assertTrue($reflectionClassFormatter->isHandling($reflectionClass));
        $this->assertSame(
            sprintf(
                '\\ReflectionClass (\\%s)',
                Interface06bcfa5ec18d11edafa10242ac120002::class,
            ),
            $reflectionClassFormatter->format($caster, $reflectionClass),
        );
        $this->assertSame(
            sprintf(
                '\\ReflectionClass ((interface) \\%s)',
                Interface06bcfa5ec18d11edafa10242ac120002::class,
            ),
            $reflectionClassFormatter->format($caster->withIsPrependingType(true), $reflectionClass),
        );
    }

    public function testFormatWorksWithReflectionClassArgumentWhenItContainsATraitReference(): void
    {
        $caster = Caster::create();
        $reflectionClassFormatter = new ReflectionClassFormatter();
        $reflectionClass = new ReflectionClass(Trait06bcf914c18d11edafa10242ac120002::class);

        $this->assertTrue($reflectionClass->isTrait());
        $this->assertTrue($reflectionClassFormatter->isHandling($reflectionClass));
        $this->assertSame(
            sprintf(
                '\\ReflectionClass (\\%s)',
                Trait06bcf914c18d11edafa10242ac120002::class,
            ),
            $reflectionClassFormatter->format($caster, $reflectionClass),
        );
        $this->assertSame(
            sprintf(
                '\\ReflectionClass ((trait) \\%s)',
                Trait06bcf914c18d11edafa10242ac120002::class,
            ),
            $reflectionClassFormatter->format($caster->withIsPrependingType(true), $reflectionClass),
        );
    }

    public function testWithIsWrappingInClassNameWorks(): void
    {
        $reflectionClassFormatterA = new ReflectionClassFormatter();
        $reflectionClassFormatterB = $reflectionClassFormatterA->withIsWrappingInClassName(false);

        $this->assertNotSame($reflectionClassFormatterA, $reflectionClassFormatterB);
        $this->assertTrue($reflectionClassFormatterA->isWrappingInClassName());
        $this->assertFalse($reflectionClassFormatterB->isWrappingInClassName());
    }
}
