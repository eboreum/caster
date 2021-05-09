<?php

declare(strict_types=1);

namespace Eboreum\Caster\Exception;

use Eboreum\Caster\Contract\Formatter\FormatterInterface;

/**
 * An exception used with FormatterInterface, denoting that something formatting related has gone awry.
 */
class FormatterException extends RuntimeException
{

}
