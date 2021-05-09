<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Test\Unit;

use PHPUnit\Framework\TestCase;

class AllExampleScriptsWorkTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testAllExampleScriptsWork(): void
    {
        $filePaths = [];

        foreach (glob(TEST_ROOT_PATH . "/../script/misc/readme/example-*.php") as $filePath) {
            if (false === is_file($filePath)) {
                continue;
            }

            $filePaths[] = $filePath;
        }

        $this->assertGreaterThan(0, count($filePaths));

        foreach ($filePaths as $filePath) {
            ob_start();
            include $filePath;
            $output = ob_get_contents();
            ob_end_clean();

            $this->assertGreaterThan(0, mb_strlen($output), "File: {$filePath}");
        }
    }
}
