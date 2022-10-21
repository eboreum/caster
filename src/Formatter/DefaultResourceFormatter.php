<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\CasterInterface;

use function get_resource_type;
use function sprintf;

class DefaultResourceFormatter extends AbstractResourceFormatter
{
    public function format(CasterInterface $caster, Resource_ $resource): ?string
    {
        return sprintf(
            '`%s` %s',
            get_resource_type($resource->getResource()),
            (string)$resource->getResource(),
        );
    }
}
