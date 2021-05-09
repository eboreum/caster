<?php

declare(strict_types = 1); // README.md.remove

use Eboreum\Caster\Caster;

require_once dirname(__DIR__, 2) . "/bootstrap.php"; // README.md.remove

$caster = Caster::create();

echo sprintf(
    "%s\n%s\n%s\n%s",
    $caster->cast(null),
    $caster->cast(true),
    $caster->cast("foo"),
    $caster->cast(new \stdClass)
);

$caster = $caster->withIsPrependingType(true);

echo "\n\n";

echo sprintf(
    "%s\n%s\n%s\n%s",
    $caster->cast(null),
    $caster->cast(true),
    $caster->cast("foo"),
    $caster->cast(new \stdClass)
);
