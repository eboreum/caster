<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Common\DataType\Resource;
use Eboreum\Caster\Contract\CasterInterface;

class DefaultResourceFormatter extends AbstractResourceFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, Resource $resource): ?string
    {
        return sprintf(
            "`%s` {$resource->getResource()}",
            get_resource_type($resource->getResource()),
        );
    }
}
