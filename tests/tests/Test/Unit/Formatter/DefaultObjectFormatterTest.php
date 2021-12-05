<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use PHPUnit\Framework\TestCase;

class DefaultObjectFormatterTest extends TestCase
{
    /**
     * @dataProvider dataProvier_testBasics
     */
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        Caster $caster,
        object $object
    ): void {
        $defaultObjectFormatter = new DefaultObjectFormatter();

        $this->assertTrue($defaultObjectFormatter->isHandling($object), $message);

        $formatted = $defaultObjectFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted));

        $this->assertMatchesRegularExpression($expected, $formatted, $message);

        $caster = $caster->withIsPrependingType(true);
        $formatted = $defaultObjectFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression($expectedWithType, $formatted, $message);

        $this->assertFalse($defaultObjectFormatter->isAppendingSplObjectHash());
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: Caster, 4: \stdClass}>
     */
    public function dataProvier_testBasics(): array
    {
        return [
            [
                'stdClass',
                '/^\\\\stdClass$/',
                '/^\\\\stdClass$/',
                Caster::getInstance(),
                new \stdClass(),
            ],
        ];
    }

    public function testWithIsAppendingSplObjectHashWorks(): void
    {
        $caster = Caster::getInstance();
        $object = new \DateTimeImmutable();

        $defaultObjectFormatterA = new DefaultObjectFormatter();
        $defaultObjectFormatterB = $defaultObjectFormatterA->withIsAppendingSplObjectHash(false);
        $defaultObjectFormatterC = $defaultObjectFormatterA->withIsAppendingSplObjectHash(true);

        $this->assertNotSame($defaultObjectFormatterA, $defaultObjectFormatterB);
        $this->assertNotSame($defaultObjectFormatterA, $defaultObjectFormatterC);
        $this->assertNotSame($defaultObjectFormatterB, $defaultObjectFormatterC);

        $this->assertFalse($defaultObjectFormatterA->isAppendingSplObjectHash());
        $formatted = $defaultObjectFormatterA->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression('/^\\\\DateTimeImmutable$/', $formatted);

        $this->assertFalse($defaultObjectFormatterB->isAppendingSplObjectHash());
        $formatted = $defaultObjectFormatterB->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression('/^\\\\DateTimeImmutable$/', $formatted);

        $this->assertTrue($defaultObjectFormatterC->isAppendingSplObjectHash());
        $formatted = $defaultObjectFormatterC->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression('/^\\\\DateTimeImmutable \([0-9a-f]+\)$/', $formatted);
    }
}
