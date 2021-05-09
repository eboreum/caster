<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Abstraction\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Resource;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;

abstract class AbstractResourceFormatter extends AbstractFormatter implements ResourceFormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function isHandling(Resource $resource): bool
    {
        return true;
    }
}
