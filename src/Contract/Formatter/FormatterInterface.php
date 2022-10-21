<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\Formatter;

use Eboreum\Caster\Contract\Collection\ElementInterface;
use Eboreum\Caster\Contract\ImmutableObjectInterface;

interface FormatterInterface extends ImmutableObjectInterface, ElementInterface
{
}
