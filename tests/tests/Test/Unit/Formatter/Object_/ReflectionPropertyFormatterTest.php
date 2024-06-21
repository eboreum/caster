<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Attribute\SensitiveProperty;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Formatter\Object_\ReflectionPropertyFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use ReflectionProperty;
use stdClass;

use function assert;
use function is_string;
use function preg_quote;
use function sprintf;

#[CoversClass(ReflectionPropertyFormatter::class)]
class ReflectionPropertyFormatterTest extends TestCase
{
    // @phpstan-ignore-next-line
    private static $test1b5d80f8c3e711edafa10242ac120002; // phpcs:ignore

    /**
     * @return array<array{string, string, Caster, ReflectionPropertyFormatter, ReflectionProperty}>
     */
    public static function providerTestFormatWorksWithReflectionProperty(): array
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
            (static function (): array {
                $object = new class
                {
                    #[SensitiveProperty]
                    private string $foo = 'bar'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
                };

                return [
                    'Has sensitive property.',
                    sprintf(
                        '/^\\\\ReflectionProperty \(%s->\$foo = %s\)$/',
                        preg_quote(Caster::makeNormalizedClassName(new ReflectionObject($object)), '/'),
                        preg_quote(CasterInterface::SENSITIVE_MESSAGE_DEFAULT, '/'),
                    ),
                    Caster::create()->withIsPrependingType(true),
                    new ReflectionPropertyFormatter(),
                    new ReflectionProperty($object, 'foo'),
                ];
            })(),
        ];
    }

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

    #[DataProvider('providerTestFormatWorksWithReflectionProperty')]
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
