<?php

declare(strict_types = 1); // README.md.remove

use Eboreum\Caster\Abstraction\Formatter\AbstractStringFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;

require_once dirname(__DIR__, 2) . "/bootstrap.php"; // README.md.remove

$caster = Caster::create();
$caster = $caster->withCustomStringFormatterCollection(new StringFormatterCollection([
    /**
     * @inheritDoc
     */
    new class extends AbstractStringFormatter
    {
        /**
         * {@inheritDoc}
         */
        public function format(CasterInterface $caster, string $string): ?string
        {
            if (false === $this->isHandling($string)) {
                return null; // Pass on to next formatter or lastly DefaultStringFormatter
            }

            if ("What do we like?" === (string)$string) {
                return $caster->cast("CAKE!");
            }

            return null; // Pass on to next formatter or lastly DefaultStringFormatter
        }

        /**
         * {@inheritDoc}
         */
        public function isHandling(string $string): bool
        {
            return true;
        }
    },
]));

echo $caster->cast("What do we like?") . "\n";

echo $caster->castTyped("Mmmm, cake") . "\n";
