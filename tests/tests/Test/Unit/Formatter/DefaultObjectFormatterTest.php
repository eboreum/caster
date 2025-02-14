<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use DateTimeImmutable;
use Eboreum\Caster\Abstraction\Formatter\AbstractObjectTypeFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(AbstractObjectTypeFormatter::class)]
#[CoversClass(DefaultObjectFormatter::class)]
class DefaultObjectFormatterTest extends TestCase
{
    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: Caster, 4: stdClass}>
     */
    public static function providerTestBasics(): array
    {
        return [
            [
                'stdClass',
                '/^\\\\stdClass$/',
                '/^\\\\stdClass \([0-9a-f]+\)$/',
                Caster::getInstance(),
                new stdClass(),
            ],
        ];
    }

    #[DataProvider('providerTestBasics')]
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithAppendedSplObjectHash,
        Caster $caster,
        object $object,
    ): void {
        $defaultObjectFormatter = new DefaultObjectFormatter();

        $this->assertTrue($defaultObjectFormatter->isHandling($object), $message);

        $formatted = $defaultObjectFormatter->format($caster, $object);
        $this->assertIsString($formatted);

        $this->assertFalse($defaultObjectFormatter->isAppendingSplObjectHash());
        $this->assertMatchesRegularExpression($expected, $formatted, $message);

        $defaultObjectFormatter = $defaultObjectFormatter->withIsAppendingSplObjectHash(true);
        $this->assertTrue($defaultObjectFormatter->isAppendingSplObjectHash());

        $formatted = $defaultObjectFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression($expectedWithAppendedSplObjectHash, $formatted, $message);
    }

    public function testWithIsAppendingSplObjectHashWorks(): void
    {
        $caster = Caster::getInstance();
        $object = new DateTimeImmutable();

        $defaultObjectFormatterA = new DefaultObjectFormatter();
        $defaultObjectFormatterB = $defaultObjectFormatterA->withIsAppendingSplObjectHash(false);
        $defaultObjectFormatterC = $defaultObjectFormatterA->withIsAppendingSplObjectHash(true);

        $this->assertNotSame($defaultObjectFormatterA, $defaultObjectFormatterB);
        $this->assertNotSame($defaultObjectFormatterA, $defaultObjectFormatterC);
        $this->assertNotSame($defaultObjectFormatterB, $defaultObjectFormatterC);

        $this->assertFalse($defaultObjectFormatterA->isAppendingSplObjectHash());
        $formatted = $defaultObjectFormatterA->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression('/^\\\\DateTimeImmutable$/', $formatted);

        $this->assertFalse($defaultObjectFormatterB->isAppendingSplObjectHash());
        $formatted = $defaultObjectFormatterB->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression('/^\\\\DateTimeImmutable$/', $formatted);

        $this->assertTrue($defaultObjectFormatterC->isAppendingSplObjectHash());
        $formatted = $defaultObjectFormatterC->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression('/^\\\\DateTimeImmutable \([0-9a-f]+\)$/', $formatted);
    }
}
