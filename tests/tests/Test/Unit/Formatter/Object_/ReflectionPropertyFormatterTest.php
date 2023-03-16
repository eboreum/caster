<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\ReflectionPropertyFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

use function assert;
use function is_string;
use function preg_quote;
use function sprintf;

class ReflectionPropertyFormatterTest extends TestCase
{
    // @phpstan-ignore-next-line
    private static $test1b5d80f8c3e711edafa10242ac120002; // phpcs:ignore

    // @phpstan-ignore-next-line
    private $testdb97c7b2c3e611edafa10242ac120002; // phpcs:ignore

    public function testFormatWorksWithNonReflectionProperty(): void
    {
        $caster = Caster::create();
        $reflectionPropertyFormatter = new ReflectionPropertyFormatter();
        $object = new stdClass();

        $this->assertFalse($reflectionPropertyFormatter->isHandling($object));
        $this->assertNull($reflectionPropertyFormatter->format($caster, $object));
    }

    /**
     * @dataProvider dataProviderTestFormatWorksWithReflectionProperty
     */
    public function testFormatWorksWithReflectionProperty(
        string $message,
        string $expectedRegex,
        Caster $caster,
        ReflectionPropertyFormatter $reflectionPropertyFormatter,
        ReflectionProperty $reflectionProperty,
    ): void {
        $this->assertTrue($reflectionPropertyFormatter->isHandling($reflectionProperty), $message);

        $formatted = $reflectionPropertyFormatter->format($caster, $reflectionProperty);

        $this->assertIsString($formatted);
        assert(is_string($formatted));
        $this->assertMatchesRegularExpression($expectedRegex, $formatted, $message);
    }

    /**
     * @return array<array{string, string, Caster, ReflectionPropertyFormatter, ReflectionProperty}>
     */
    public function dataProviderTestFormatWorksWithReflectionProperty(): array
    {
        return [
            [
                'A static property with no type and a no default value. Type not prepended.',
                sprintf(
                    '/^\\\\ReflectionProperty \(\\\\%s::\$test1b5d80f8c3e711edafa10242ac120002 = null\)$/',
                    preg_quote(self::class, '/'),
                ),
                Caster::create(),
                new ReflectionPropertyFormatter(),
                new ReflectionProperty(self::class, 'test1b5d80f8c3e711edafa10242ac120002'),
            ],
            [
                'A non-static property with type bool and a default value. Type not prepended.',
                sprintf(
                    '/^\\\\ReflectionProperty \(\\\\%1$s::\$instance = null\)$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create(),
                new ReflectionPropertyFormatter(),
                new ReflectionProperty(Caster::class, 'instance'),
            ],
            [
                'A non-static property with type bool and a default value. Type is prepended.',
                sprintf(
                    '/^\\\\ReflectionProperty \(\\?\\\\%s \\\\%1$s::\$instance = \(null\) null\)$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create()->withIsPrependingType(true),
                new ReflectionPropertyFormatter(),
                new ReflectionProperty(Caster::class, 'instance'),
            ],
            [
                'A non-static property with no type and a no default value. Type not prepended.',
                sprintf(
                    '/^\\\\ReflectionProperty \(\\\\%s->\$testdb97c7b2c3e611edafa10242ac120002 = null\)$/',
                    preg_quote(self::class, '/'),
                ),
                Caster::create(),
                new ReflectionPropertyFormatter(),
                new ReflectionProperty(self::class, 'testdb97c7b2c3e611edafa10242ac120002'),
            ],
            [
                'A non-static property with type bool and a default value. Type not prepended.',
                sprintf(
                    '/^\\\\ReflectionProperty \(\\\\%s->\$isPrependingType = false\)$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create(),
                new ReflectionPropertyFormatter(),
                new ReflectionProperty(Caster::class, 'isPrependingType'),
            ],
            [
                'A non-static property with type bool and a default value. Type is prepended.',
                sprintf(
                    '/^\\\\ReflectionProperty \(bool \\\\%s->\$isPrependingType = \(bool\) false\)$/',
                    preg_quote(Caster::class, '/'),
                ),
                Caster::create()->withIsPrependingType(true),
                new ReflectionPropertyFormatter(),
                new ReflectionProperty(Caster::class, 'isPrependingType'),
            ],
        ];
    }

    public function testWithIsWrappingInClassNameWorks(): void
    {
        $reflectionPropertyFormatterA = new ReflectionPropertyFormatter();
        $reflectionPropertyFormatterB = $reflectionPropertyFormatterA->withIsWrappingInClassName(false);

        $this->assertNotSame($reflectionPropertyFormatterA, $reflectionPropertyFormatterB);
        $this->assertTrue($reflectionPropertyFormatterA->isWrappingInClassName());
        $this->assertFalse($reflectionPropertyFormatterB->isWrappingInClassName());
    }

    public function testWithReflectionTypeFormatterWorks(): void
    {
        $reflectionPropertyFormatterA = new ReflectionPropertyFormatter();
        $reflectionTypeFormatterA = $reflectionPropertyFormatterA->getReflectionTypeFormatter();
        $reflectionTypeFormatterB = $this->mockReflectionTypeFormatter();
        $reflectionPropertyFormatterB = $reflectionPropertyFormatterA
            ->withReflectionTypeFormatter($reflectionTypeFormatterB);

        $this->assertNotSame($reflectionPropertyFormatterA, $reflectionPropertyFormatterB);
        $this->assertNotSame(
            $reflectionPropertyFormatterA->getReflectionTypeFormatter(),
            $reflectionPropertyFormatterB->getReflectionTypeFormatter(),
        );
        $this->assertSame(
            $reflectionTypeFormatterA,
            $reflectionPropertyFormatterA->getReflectionTypeFormatter(),
        );
        $this->assertSame(
            $reflectionTypeFormatterB,
            $reflectionPropertyFormatterB->getReflectionTypeFormatter(),
        );
    }

    private function mockReflectionTypeFormatter(): ReflectionTypeFormatter&MockObject
    {
        return $this
            ->getMockBuilder(ReflectionTypeFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
