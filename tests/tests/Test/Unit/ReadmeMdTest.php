<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use PHPUnit\Framework\TestCase;

class ReadmeMdTest extends TestCase
{
    private string $contents;

    public function setUp(): void
    {
        $readmeFilePath = dirname(TEST_ROOT_PATH) . '/README.md';

        $this->assertTrue(is_file($readmeFilePath), 'README.md does not exist!');

        $contents = file_get_contents($readmeFilePath);

        assert(is_string($contents));

        $this->contents = $contents;
    }

    /**
     * Did we leave remember to update the contents of README.md?
     */
    public function testIsReadmeMdUpToDate(): void
    {
        ob_start();
        include dirname(TEST_ROOT_PATH) . '/script/make-readme.php';
        $producedContents = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(
            $this->contents === $producedContents,
            'README.md is not up–to-date. Please run: php script/make-readme.php',
        );
    }

    public function testDoesReadmeMdContainLocalFilePaths(): void
    {
        $split = preg_split('/([\\\\\/])/', PROJECT_ROOT_DIRECTORY_PATH);

        $this->assertIsArray($split);
        assert(is_array($split)); // Make phpstan happy

        if ('' === ($split[0] ?? null)) {
            array_shift($split);
        }

        $wrapAndImplode = static function (string ...$strings) {
            $inner = '(\\\\+\/|\\\\+|\/)'; // Handle both Windows and Unix

            return sprintf(
                '/%s%s%s/',
                $inner,
                implode(
                    $inner,
                    array_map(
                        static function (string $v) {
                            return preg_quote($v, '/');
                        },
                        $strings,
                    ),
                ),
                $inner,
            );
        };

        $rootPathRegex = $wrapAndImplode(...$split);

        $this->assertSame(
            0,
            preg_match($rootPathRegex, $this->contents),
            'README.md contains local file paths (on your system) and it should not.',
        );
    }
}
