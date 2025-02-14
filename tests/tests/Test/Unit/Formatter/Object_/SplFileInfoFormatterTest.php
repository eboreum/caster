<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\SplFileInfoFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use stdClass;

#[CoversClass(SplFileInfoFormatter::class)]
class SplFileInfoFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $splFileInfoFormatter = new SplFileInfoFormatter();
        $object = new stdClass();

        $this->assertFalse($splFileInfoFormatter->isHandling($object));
        $this->assertNull($splFileInfoFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $splFileInfoFormatter = new SplFileInfoFormatter();

        $object = new SplFileInfo(__FILE__);

        $this->assertTrue($splFileInfoFormatter->isHandling($object));
        $formatted = $splFileInfoFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression(
            '/^\\\\SplFileInfo \(".+"\)$/',
            $formatted,
        );
    }
}
