<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\Formatter;

use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\CasterInterface;

/**
 * @inheritDoc
 */
interface ResourceFormatterInterface extends FormatterInterface
{
    /**
     * {@inheritDoc}
     *
     * Apply logic, which handles resource-to-string conversion.
     *
     * When `null` is returned, the next custom formatter is called. If all custom formatters return `null`, the
     * default string formatter logic (`DefaultResourceFormatter`) is applied.
     *
     * If the argument $resource is not accepted by `isHandling`, this method must return `null`.
     */
    public function format(CasterInterface $caster, Resource_ $resource): ?string;

    /**
     * Whether or not the $resource argument is qualified to be handled by the formatter class implementing this
     * interface.
     */
    public function isHandling(Resource_ $resource): bool;
}
