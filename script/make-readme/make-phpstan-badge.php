<?php

declare(strict_types = 1);

$contents = file_get_contents(dirname(__DIR__, 2) . '/phpstan.neon');

if (!$contents) {
    throw new Exception('phpstan.neon not found');
}

preg_match('/\n +level: *(\d+) *\n/', $contents, $match);

if (!$match || false === is_string($match[1] ?? null)) {
    throw new Exception('Could not locate phpstan level');
}

echo sprintf(
    '![PHPStan](https://img.shields.io/badge/PHPStan-Level%%20%d-brightgreen.svg?style=flat)',
    intval($match[1]),
);
