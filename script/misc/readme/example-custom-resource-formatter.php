<?php

declare(strict_types = 1); // README.md.remove

use Eboreum\Caster\Abstraction\Formatter\AbstractFormatter;
use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\CasterInterface;

require_once dirname(__DIR__, 2) . "/bootstrap.php"; // README.md.remove

$caster = Caster::create();

$caster = $caster->withCustomResourceFormatterCollection(new ResourceFormatterCollection([
    /**
     * @inheritDoc
     */
    new class extends AbstractResourceFormatter
    {
        /**
         * {@inheritDoc}
         */
        public function format(CasterInterface $caster, Resource_ $resource): ?string
        {
            if (false === $this->isHandling($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }

            if ("stream" === get_resource_type($resource->getResource())) {
                return sprintf(
                    "opendir/fopen/tmpfile/popen/fsockopen/pfsockopen %s",
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
    /**
     * @inheritDoc
     */
    new class extends AbstractResourceFormatter
    {
        /**
         * {@inheritDoc}
         */
        public function format(CasterInterface $caster, Resource_ $resource): ?string
        {
            if (false === $this->isHandling($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }

            if ("xml" === get_resource_type($resource->getResource())) {
                $identifier = preg_replace(
                    '/^(Resource id) #\d+$/',
                    '$1 #42',
                    (string)$resource->getResource(),
                );

                assert(is_string($identifier));

                return sprintf(
                    "XML %s",
                    $identifier,
                );
            }

            return null; // Pass on to next formatter or lastly DefaultResourceFormatter
        }
    },
]));

echo $caster->cast(fopen(__FILE__, "r+")) . "\n";
