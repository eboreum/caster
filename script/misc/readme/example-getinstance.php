<?php

declare(strict_types=1);

namespace My\Application;

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Caster as EboreumCaster;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;

use function assert;
use function dirname;
use function json_encode;
use function sprintf;

require_once dirname(__DIR__, 2) . '/bootstrap.php'; // README.md.remove

class Caster extends EboreumCaster
{
    private static ?Caster $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self(CharacterEncoding::getInstance());

            /** @var array<ArrayFormatterInterface> $formatters */
            $formatters = [
                new class extends AbstractArrayFormatter
                    {
                    public function format(CasterInterface $caster, array $array): string
                    {
                        return 'I am an array!';
                    }

                    public function isHandling(array $array): bool
                    {
                        return true;
                    }
                },
            ];

            $instance = self::$instance->withCustomArrayFormatterCollection(new ArrayFormatterCollection($formatters));

            self::$instance = $instance;

            // Do more custom configuring before the instance is forever locked and returned
        }

        return self::$instance;
    }
}

echo sprintf(
    'Instances \\%s::getInstance() !== \\%s::getInstance(): %s',
    EboreumCaster::class,
    Caster::class,
    json_encode(EboreumCaster::getInstance() !== Caster::getInstance()),
) . "\n";

echo sprintf(
    'But \\%s::getInstance() === \\%s::getInstance() (same): %s',
    Caster::class,
    Caster::class,
    json_encode(Caster::getInstance() === Caster::getInstance()),
) . "\n";
