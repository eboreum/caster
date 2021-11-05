<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\Caster;
use Nette\Neon\Neon;
use PHPUnit\Framework\TestCase;

class PHPStanLevel7Test extends TestCase
{
    public function testPHPStanIsAtLevel7(): void
    {
        $phpstanNeonFilePath = PROJECT_ROOT_DIRECTORY_PATH . '/phpstan.neon';
        $contents = file_get_contents($phpstanNeonFilePath);

        assert(is_string($contents));

        $array = Neon::decode($contents);

        if (7 !== ($array['parameters']['level'] ?? null)) {
            throw new \RuntimeException(sprintf(
                'In %s, the phpstan level must be 7, but it is not. Found: %s',
                escapeshellarg($phpstanNeonFilePath),
                Caster::getInstance()->castTyped(($array['parameters']['level'] ?? null)),
            ));
        }

        $command = sprintf(
            'cd %s && php vendor/bin/phpstan 2> /dev/null',
            escapeshellarg(PROJECT_ROOT_DIRECTORY_PATH),
        );
        $resultCode = 0;
        $output = [];

        exec($command, $output, $resultCode);

        if (0 !== $resultCode) {
            throw new \RuntimeException('phpstan is not at level 7');
        }

        $this->assertTrue(true);
    }
}
