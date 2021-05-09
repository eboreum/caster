<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Test\Unit\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Formatter\Object_\ZipArchiveFormatter;
use PHPUnit\Framework\TestCase;

class ZipArchiveFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $zipArchiveFormatter = new ZipArchiveFormatter;
        $object = new \stdClass;

        $this->assertFalse($zipArchiveFormatter->isHandling($object));
        $this->assertNull($zipArchiveFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $zipArchiveFormatter = new ZipArchiveFormatter;

        $zipArchiveFilePath = sprintf(
            "%s/resources/TestResource/Unit/Formatter/Object_/ZipArchiveFormatterTest/%s/test.zip",
            TEST_ROOT_PATH,
            __FUNCTION__,
        );

        $object = new \ZipArchive;
        $object->open($zipArchiveFilePath);

        $this->assertTrue($zipArchiveFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            sprintf(
                implode("", [
                    '/',
                    '^',
                    '\\\\ZipArchive \{',
                        '\$status = 0',
                        ', \$statusSys = 0',
                        ', \$numFiles = 1',
                        ', \$filename = ".+\/test.zip"',
                        ', \$comment = ""',
                    '\}',
                    '$',
                    '/',
                ]),
                $zipArchiveFilePath,
            ),
            $zipArchiveFormatter->format($caster,  $object),
        );
    }
}
