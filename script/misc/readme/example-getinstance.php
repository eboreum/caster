<?php

declare(strict_types = 1); // README.md.remove

namespace My\Application;

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Caster as EboreumCaster;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;

require_once dirname(__DIR__, 2) . "/bootstrap.php"; // README.md.remove

class Caster extends EboreumCaster
{
    private static ?Caster $instance = null;

    public static function getInstance(): Caster // PHP 7.4 Liskov substitution will allow this
    {
        if (null === self::$instance) {
            self::$instance = static::create();

            self::$instance = self::$instance->withCustomArrayFormatterCollection(new ArrayFormatterCollection(...[
                new class extends AbstractArrayFormatter
                {
                    /**
                     * {@inheritDoc}
                     */
                    public function format(CasterInterface $caster, array $array): ?string
                    {
                        return "I am an array!";
                    }

                    /**
                     * {@inheritDoc}
                     */
                    public function isHandling(array $array): bool
                    {
                        return true;
                    }
                }
            ]));

            // Do more custom configuring before the instance is forever locked and returned
        }

        return self::$instance;
    }
}

echo sprintf(
    "Instances \\%s::getInstance() !== \\%s::getInstance(): %s",
    EboreumCaster::class,
    Caster::class,
    json_encode(EboreumCaster::getInstance() !== Caster::getInstance()),
) . "\n";

echo sprintf(
    "But \\%s::getInstance() === \\%s::getInstance() (same): %s",
    Caster::class,
    Caster::class,
    json_encode(Caster::getInstance() === Caster::getInstance()),
) . "\n";
