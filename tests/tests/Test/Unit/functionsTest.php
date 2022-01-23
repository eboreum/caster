<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use PHPUnit\Framework\TestCase;

use function Eboreum\Caster\functions\rglob;

class functionsTest extends TestCase
{
    public function test_rglob_works(): void
    {
        $filePaths = rglob(dirname(TEST_ROOT_PATH) . '/src/*.php');

        $this->assertGreaterThan(0, $filePaths);

        $this->assertTrue(in_array(
            dirname(TEST_ROOT_PATH) . '/src/Caster.php',
            $filePaths,
            true,
        ));

        $this->assertTrue(in_array(
            dirname(TEST_ROOT_PATH) . '/src/Contract/ImmutableObjectInterface.php',
            $filePaths,
            true,
        ));
    }
}
