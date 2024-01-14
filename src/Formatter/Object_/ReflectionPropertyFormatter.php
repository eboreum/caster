<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Attribute\SensitiveProperty;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionClass;
use ReflectionProperty;

use function assert;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formats instances of \ReflectionProperty.
 *
 * Notice: Properties specified through Constructor Promotion
 * (https://php.watch/versions/8.0/constructor-property-promotion) will also end up as a ReflectionProperty when calling
 * method such as ReflectionClass->getProperties() or ReflectionClass->getProperty(...).
 *
 * @see https://www.php.net/manual/en/class.reflectionproperty.php
 */
class ReflectionPropertyFormatter extends AbstractObjectFormatter
{
    protected bool $isWrappingInClassName = true;

    protected ReflectionTypeFormatter $reflectionTypeFormatter;

    public function __construct()
    {
        $this->reflectionTypeFormatter = (new ReflectionTypeFormatter())->withIsWrappingInClassName(false);
    }

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof ReflectionProperty); // Make phpstan happy

        $str = sprintf(
            '%s%s$%s',
            Caster::makeNormalizedClassName($object->getDeclaringClass()),
            ($object->isStatic() ? '::' : '->'),
            $object->getName(),
        );

        /** @var bool $isSensitive */
        $isSensitive = (bool) ($object->getAttributes(SensitiveProperty::class)[0] ?? false);

        if ($isSensitive) {
            $str .= ' = ' . $caster->getSensitiveMessage();
        } else {
            if ($caster->isPrependingType() && $object->getType()) {
                $str = $this->getReflectionTypeFormatter()->format($caster, $object->getType()) . ' ' . $str;
            }

            if ($object->hasDefaultValue()) {
                $str .= ' = ' . $caster->cast($object->getDefaultValue());
            }
        }

        if ($this->isWrappingInClassName()) {
            $str = sprintf(
                '%s (%s)',
                Caster::makeNormalizedClassName(new ReflectionClass($object)),
                $str,
            );
        }

        return $str;
    }

    public function getReflectionTypeFormatter(): ReflectionTypeFormatter
    {
        return $this->reflectionTypeFormatter;
    }

    public function isHandling(object $object): bool
    {
        return ($object instanceof ReflectionProperty);
    }

    public function isWrappingInClassName(): bool
    {
        return $this->isWrappingInClassName;
    }

    /**
     * Returns a clone.
     */
    public function withReflectionTypeFormatter(ReflectionTypeFormatter $reflectionTypeFormatter): self
    {
        $clone = clone $this;
        $clone->reflectionTypeFormatter = $reflectionTypeFormatter;

        return $clone;
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
