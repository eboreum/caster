<?php

declare(strict_types = 1); // README.md.remove

use Eboreum\Caster\Caster;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Common\DataType\String_\Character;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;

require_once dirname(__DIR__, 2) . "/bootstrap.php"; // README.md.remove

$caster = Caster::create();
$caster = $caster->withDepthMaximum(new PositiveInteger(2));
$caster = $caster->withArraySampleSize(new UnsignedInteger(3));
$caster = $caster->withStringSampleSize(new UnsignedInteger(4));
$caster = $caster->withStringQuotingCharacter(new Character("`"));

echo "\$caster->getDepthMaximum()->toInteger(): " . $caster->getDepthMaximum()->toInteger() . "\n";
echo "\$caster->getArraySampleSize()->toInteger(): " . $caster->getArraySampleSize()->toInteger() . "\n";
echo "\$caster->getStringSampleSize()->toInteger(): " . $caster->getStringSampleSize()->toInteger() . "\n";
echo "\$caster->getStringQuotingCharacter(): " . $caster->getStringQuotingCharacter() . "\n";
