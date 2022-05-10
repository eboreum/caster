<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\Caster;
use PHPUnit\Framework\TestCase;

class PHPStanAtDesiredLevelTest extends TestCase
{
    public function testPHPStanIsAtDesiredLevel(): void
    {
        $command = sprintf(
            'cd %s && php vendor/bin/phpstan 2> /dev/null',
            escapeshellarg(PROJECT_ROOT_DIRECTORY_PATH),
        );
        $resultCode = 0;
        $output = [];

        exec($command, $output, $resultCode);

        if (0 !== $resultCode) {
            throw new \RuntimeException('phpstan is not at the level specified in phpstan.neon');
        }

        $this->assertTrue(true);
    }
}
