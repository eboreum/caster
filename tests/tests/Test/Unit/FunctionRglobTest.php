<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use PHPUnit\Framework\TestCase;

class FunctionRglobTest extends TestCase
{
    public function testCompareContents(): void
    {
        $filePaths = \Eboreum\Caster\rglob(dirname(TEST_ROOT_PATH) . "/src/*.php");

        $this->assertGreaterThan(0, $filePaths);

        $this->assertTrue(in_array(
            dirname(TEST_ROOT_PATH) . "/src/Caster.php",
            $filePaths,
            true,
        ));

        $this->assertTrue(in_array(
            dirname(TEST_ROOT_PATH) . "/src/Contract/ImmutableObjectInterface.php",
            $filePaths,
            true,
        ));
    }
}
