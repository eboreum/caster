<?php

declare(strict_types = 1); // README.md.remove

use Eboreum\Caster\Caster;

require_once dirname(__DIR__, 2) . "/bootstrap.php"; // README.md.remove

/**
 * @throws \InvalidArgumentException
 */
function foo(mixed $value): void {
    if (false === is_string($value) && false === is_int($value)) {
        throw new \InvalidArgumentException(sprintf(
            "Expects argument \$value to be a string or an integer. Found: %s",
            Caster::create()->castTyped($value),
        ));
    }
};

try {
    foo(["bar"]);
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
}
