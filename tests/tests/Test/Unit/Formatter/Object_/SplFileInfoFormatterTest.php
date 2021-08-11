<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Formatter\Object_\SplFileInfoFormatter;
use PHPUnit\Framework\TestCase;

class SplFileInfoFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $splFileInfoFormatter = new SplFileInfoFormatter;
        $object = new \stdClass;

        $this->assertFalse($splFileInfoFormatter->isHandling($object));
        $this->assertNull($splFileInfoFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $splFileInfoFormatter = new SplFileInfoFormatter;

        $object = new \SplFileInfo(__FILE__);

        $this->assertTrue($splFileInfoFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            '/^\\\\SplFileInfo \(".+"\)$/',
            $splFileInfoFormatter->format($caster, $object),
        );
    }
}
