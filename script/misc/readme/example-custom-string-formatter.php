<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractStringFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\StringFormatterInterface;

require_once dirname(__DIR__, 2) . '/bootstrap.php'; // README.md.remove

/** @var array<StringFormatterInterface> $formatters */
$formatters = [
    new class extends AbstractStringFormatter
    {
        public function format(CasterInterface $caster, string $string): ?string
        {
            if (false === $this->isHandling($string)) {
                return null; // Pass on to next formatter or lastly DefaultStringFormatter
            }

            if ('What do we like?' === (string)$string) {
                return $caster->cast('CAKE!');
            }

            return null; // Pass on to next formatter or lastly DefaultStringFormatter
        }

        public function isHandling(string $string): bool
        {
            return true;
        }
    },
];

$caster = Caster::create();
$caster = $caster->withCustomStringFormatterCollection(new StringFormatterCollection($formatters));

echo $caster->cast('What do we like?') . "\n";

echo $caster->castTyped('Mmmm, cake') . "\n";
