<?php

declare(strict_types = 1); // README.md.remove

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\EncryptedString;

require_once dirname(__DIR__, 2) . "/bootstrap.php"; // README.md.remove

$caster = Caster::create();
$caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
    new EncryptedString("bar"),
    new EncryptedString("bim"),
    new EncryptedString("345"),
    new EncryptedString("456"),
]));

echo $caster->castTyped("foo bar baz bim bum") . "\n"; // Notice: Original string length is not revealed

echo "\n\n";

echo $caster->castTyped("0123456789") . "\n"; // Notice: 3456 are masked because 345 and 456 overlap
