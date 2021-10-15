<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AllExampleScriptsWorkTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testAllExampleScriptsWork(): void
    {
        $globbedFilePaths = glob(TEST_ROOT_PATH . '/../script/misc/readme/example-*.php');

        assert(is_array($globbedFilePaths));

        $filePaths = [];

        foreach ($globbedFilePaths as $filePath) {
            if (false === is_file($filePath)) {
                continue;
            }

            $filePaths[] = $filePath;
        }

        $this->assertGreaterThan(0, count($filePaths));

        foreach ($filePaths as $filePath) {
            try {
                ob_start();
                include $filePath;
                $output = ob_get_contents();
                ob_end_clean();
            } catch (\Throwable $t) {
                throw new \RuntimeException(sprintf(
                    'Failure when processing file: %s',
                    $filePath,
                ), 0, $t);
            }

            assert(is_string($output));

            $this->assertGreaterThan(0, mb_strlen($output), "File: {$filePath}");
        }
    }
}
