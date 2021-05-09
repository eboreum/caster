<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Caster;

/**
 * Formatter for \ZipArchive.
 */
class ZipArchiveFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        $stringifiedProperties = [];

        foreach (get_object_vars($object) as $propertyName => $value) {
            $value = $caster->cast($value);

            $stringifiedProperties[] = sprintf(
                "\$%s = %s",
                $propertyName,
                $value,
            );
        }

        return sprintf(
            "%s {%s}",
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            implode(", ", $stringifiedProperties)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval(
            class_exists('ZipArchive')
            && $object instanceof \ZipArchive
        );
    }
}
