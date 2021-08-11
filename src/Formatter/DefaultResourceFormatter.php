<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\CasterInterface;

class DefaultResourceFormatter extends AbstractResourceFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, Resource_ $resource): ?string
    {
        return sprintf(
            "`%s` {$resource->getResource()}",
            get_resource_type($resource->getResource()),
        );
    }
}
