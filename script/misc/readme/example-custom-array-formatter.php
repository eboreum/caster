<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;

require_once dirname(__DIR__, 2) . '/bootstrap.php'; // README.md.remove

/** @var array<ArrayFormatterInterface> $formatters */
$formatters = [
    new class extends AbstractArrayFormatter
    {
        public function format(CasterInterface $caster, array $array): ?string
        {
            if (false === $this->isHandling($array)) {
                return null; // Pass on to next formatter or lastly DefaultArrayFormatter
            }

            if (1 === count($array)) {
                /*
                 * /!\ CAUTION /!\
                 * Do NOT do this in practice! You disable sensitive string masking.
                 */
                return print_r($array, true);
            }

            if (2 === count($array)) {
                return 'I am an array!';
            }

            if (3 === count($array)) {
                $array[0] = 'SURPRISE!';

                // Override and use DefaultArrayFormatter for rendering output
                return $caster->getDefaultArrayFormatter()->format($caster, $array);
            }

            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }

        public function isHandling(array $array): bool
        {
            return true;
        }
    },
];

$caster = Caster::create();
$caster = $caster->withCustomArrayFormatterCollection(new ArrayFormatterCollection($formatters));

echo $caster->cast(['foo']) . "\n";

echo $caster->cast(['foo', 'bar']) . "\n";

echo $caster->cast(['foo', 'bar', 'baz']) . "\n";

echo $caster->cast(['foo', 'bar', 'baz', 'bim']) . "\n";

echo $caster->castTyped(['foo', 'bar', 'baz', 'bim']) . "\n";
