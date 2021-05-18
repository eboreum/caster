<?php

declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Common\DataType\Resource;
use Eboreum\Caster\Formatter\DefaultResourceFormatter;
use PHPUnit\Framework\TestCase;

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
        Resource $resource
    ): void
    {
        $defaultResourceFormatter = new DefaultResourceFormatter;

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

    public function dataProvier_testBasics(): array
    {
        return [
            [
                "xml_parser_create",
                '/^`xml` Resource id #\d+$/',
                '/^`xml` Resource id #\d+$/',
                Caster::getInstance(),
                new Resource(\xml_parser_create("UTF-8")),
            ],
        ];
    }
}
