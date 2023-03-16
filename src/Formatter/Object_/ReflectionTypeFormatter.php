<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_walk;
use function assert;
use function class_exists;
use function enum_exists;
use function explode;
use function implode;
use function interface_exists;
use function ltrim;
use function trait_exists;

/**
 * @inheritDoc
 *
 * Formats instances of \ReflectionType.
 *
 * @see https://www.php.net/manual/en/class.reflectiontype.php
 */
class ReflectionTypeFormatter extends AbstractObjectFormatter
{
    public static function isClassishReference(string $str): bool
    {
        return (
            class_exists($str)
            || enum_exists($str)
            || interface_exists($str)
            || trait_exists($str)
        );
    }

    protected bool $isWrappingInClassName = true;

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof ReflectionType); // Make phpstan happy

        if ($object instanceof ReflectionNamedType) {
            $typeStrWithoutNullable = ltrim((string) $object, '?');

            if (self::isClassishReference($typeStrWithoutNullable)) {
                return ($object->allowsNull() ? '?' : '') . '\\' . $typeStrWithoutNullable;
            }

            return (string) $object;
        }

        if ($object instanceof ReflectionIntersectionType || $object instanceof ReflectionUnionType) {
            $separator = ($object instanceof ReflectionIntersectionType ? '&' : '|');
            $parts = explode($separator, (string) $object);

            array_walk($parts, static function (string &$v): void {
                if (self::isClassishReference($v)) {
                    $v = '\\' . $v;
                }
            });

            return implode($separator, $parts);
        }

        return (string) $object; // Fallback
    }

    public function isHandling(object $object): bool
    {
        return ($object instanceof ReflectionType);
    }

    public function isWrappingInClassName(): bool
    {
        return $this->isWrappingInClassName;
    }

    /**
     * Returns a clone.
     */
    public function withIsWrappingInClassName(bool $isWrappingInClassName): self
    {
        $clone = clone $this;
        $clone->isWrappingInClassName = $isWrappingInClassName;

        return $clone;
    }
}
