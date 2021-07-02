<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Abstraction\Formatter;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;

abstract class AbstractResourceFormatter extends AbstractFormatter implements ResourceFormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function isHandling(Resource_ $resource): bool
    {
        return true;
    }
}
