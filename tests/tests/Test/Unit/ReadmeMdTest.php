<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use PHPUnit\Framework\TestCase;

class ReadmeMdTest extends TestCase
{
    private string $contents;

    public function setUp(): void
    {
        $readmeFilePath = dirname(TEST_ROOT_PATH) . "/README.md";

        $this->assertTrue(is_file($readmeFilePath), "README.md does not exist!");

        $contents = file_get_contents($readmeFilePath);

        $this->assertIsString($contents);

        $this->contents = $contents;
    }

    /**
     * Did we leave remember to update the contents of README.md?
     */
    public function testIsReadmeMdUpToDate(): void
    {
        ob_start();
        include dirname(TEST_ROOT_PATH) . "/script/make-readme.php";
        $producedContents = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(
            $this->contents === $producedContents,
            "README.md is not up–to-date. Please run: php script/make-readme.php",
        );
    }

    public function testDoesReadmeMdContainLocalFilePaths(): void
    {
        $rootPath = dirname(TEST_ROOT_PATH);

        $split = preg_split('/([\\\\\/])/', $rootPath);

        assert(is_array($split));

        $rootPathRegex = sprintf(
            '/%s/',
            implode(
                '(\\\\+\/|\\\\+|\/)', // Handle both Windows and Unix
                array_map(
                    function(string $v){
                        return preg_quote($v, "/");
                    },
                    $split,
                ),
            ),
        );

        $this->assertSame(
            0,
            preg_match($rootPathRegex, $this->contents),
            "README.md contains local file paths (on your system) and it should not.",
        );
    }
}
