<?php

declare(strict_types=1);

namespace Eboreum\Caster\Abstraction\Formatter;

use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;

abstract class AbstractResourceFormatter extends AbstractFormatter implements ResourceFormatterInterface
{
    public function isHandling(Resource_ $resource): bool
    {
        return true;
    }
}
