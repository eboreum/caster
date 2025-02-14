<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;

require_once dirname(__DIR__, 2) . '/bootstrap.php'; // README.md.remove

/** @var array<ResourceFormatterInterface> $formatters */
$formatters = [
    new class extends AbstractResourceFormatter
    {
        public function format(CasterInterface $caster, Resource_ $resource): ?string
        {
            if (false === $this->isHandling($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }

            if ('stream' === get_resource_type($resource->getResource())) {
                return sprintf(
                    'opendir/fopen/tmpfile/popen/fsockopen/pfsockopen %s',
                    preg_replace(
                        '/^(Resource id) #\d+$/',
                        '$1 #42',
                        (string)$resource->getResource(),
                    ),
                );
            }

            return null; // Pass on to next formatter or lastly DefaultResourceFormatter
        }
    },
    new class extends AbstractResourceFormatter
    {
        public function format(CasterInterface $caster, Resource_ $resource): ?string
        {
            if (false === $this->isHandling($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }

            if ('xml' === get_resource_type($resource->getResource())) {
                $identifier = preg_replace(
                    '/^(Resource id) #\d+$/',
                    '$1 #42',
                    (string)$resource->getResource(),
                );

                assert(is_string($identifier));

                return sprintf(
                    'XML %s',
                    $identifier,
                );
            }

            return null; // Pass on to next formatter or lastly DefaultResourceFormatter
        }
    },
];

$caster = Caster::create();
$caster = $caster->withCustomResourceFormatterCollection(new ResourceFormatterCollection($formatters));

echo $caster->cast(fopen(__FILE__, 'r+')) . "\n";
