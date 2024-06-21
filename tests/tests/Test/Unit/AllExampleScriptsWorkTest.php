<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

use function assert;
use function count;
use function glob;
use function is_array;
use function is_file;
use function is_string;
use function mb_strlen;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function sprintf;

#[CoversNothing]
class AllExampleScriptsWorkTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testAllExampleScriptsWork(): void
    {
        $globbedFilePaths = glob(TEST_ROOT_PATH . '/../script/misc/readme/example-*.php');

        assert(is_array($globbedFilePaths)); // Make phpstan happy

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
            } catch (Throwable $t) {
                throw new RuntimeException(sprintf(
                    'Failure when processing file: %s',
                    $filePath,
                ), 0, $t);
            }

            assert(is_string($output)); // Make phpstan happy

            $this->assertGreaterThan(0, mb_strlen($output), 'File: ' . $filePath);
        }
    }
}
