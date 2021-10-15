<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Formatter\DefaultResourceFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DefaultResourceFormatterTest extends TestCase
{
    /**
     * @dataProvider dataProvier_testBasics
     */
    public function testBasics(
        string $message,
        string $expected,
        string $expectedWithType,
        Caster $caster,
        Resource_ $resource
    ): void {
        $defaultResourceFormatter = new DefaultResourceFormatter();

        $this->assertTrue($defaultResourceFormatter->isHandling($resource), $message);

        $this->assertMatchesRegularExpression(
            $expected,
            $defaultResourceFormatter->format($caster, $resource),
            $message,
        );

        $caster = $caster->withIsPrependingType(true);

        $this->assertMatchesRegularExpression(
            $expectedWithType,
            $defaultResourceFormatter->format($caster, $resource),
            $message,
        );
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: Caster, 4: Resource_}>
     */
    public function dataProvier_testBasics(): array
    {
        return [
            [
                'fopen',
                '/^`stream` Resource id #\d+$/',
                '/^`stream` Resource id #\d+$/',
                Caster::getInstance(),
                (function () {
                    $resource = \fopen(__FILE__, 'r+');

                    assert(is_resource($resource));

                    return new Resource_($resource);
                })(),
            ],
        ];
    }
}
