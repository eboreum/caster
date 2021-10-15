<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
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

        $this->assertMatchesRegularExpression(
            $expected,
            $defaultObjectFormatter->format($caster, $object),
            $message,
        );

        $caster = $caster->withIsPrependingType(true);

        $this->assertMatchesRegularExpression(
            $expectedWithType,
            $defaultObjectFormatter->format($caster, $object),
            $message,
        );

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
        $this->assertMatchesRegularExpression(
            '/^\\\\DateTimeImmutable$/',
            $defaultObjectFormatterA->format($caster, $object),
        );
        $this->assertFalse($defaultObjectFormatterB->isAppendingSplObjectHash());
        $this->assertMatchesRegularExpression(
            '/^\\\\DateTimeImmutable$/',
            $defaultObjectFormatterB->format($caster, $object),
        );
        $this->assertTrue($defaultObjectFormatterC->isAppendingSplObjectHash());
        $this->assertMatchesRegularExpression(
            '/^\\\\DateTimeImmutable \([0-9a-f]+\)$/',
            $defaultObjectFormatterC->format($caster, $object),
        );
    }
}
