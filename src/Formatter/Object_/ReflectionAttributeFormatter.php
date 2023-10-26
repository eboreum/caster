<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use SensitiveParameter;

use function assert;
use function implode;
use function is_string;
use function mb_strtolower;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formats instances of \ReflectionAttribute.
 *
 * @see https://www.php.net/manual/en/class.reflectionattribute.php
 */
class ReflectionAttributeFormatter extends AbstractObjectFormatter
{
    protected bool $isWrappingInClassName = true;

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof ReflectionAttribute); // Make phpstan happy

        $str = '';

        if ($caster->isPrependingType()) {
            $str = '(attribute) ';
        }

        /** @var ReflectionClass<object> $reflectionClassActualClass */
        $reflectionClassActualClass = new ReflectionClass($object->getName());

        $str .= Caster::makeNormalizedClassName($reflectionClassActualClass);

        /** @var array<string> $argumentsAsStrings */
        $argumentsAsStrings = [];

        foreach ($object->getArguments() as $key => $value) {
            /** @var bool $isSensitive */
            $isSensitive = false;

            /** @var ReflectionMethod|null $reflectionMethodConstructor */
            $reflectionMethodConstructor = $reflectionClassActualClass->getConstructor();

            if ($reflectionMethodConstructor) {
                /** @var ReflectionParameter|null $reflectionParameterMatchingKey */
                $reflectionParameterMatchingKey = null;

                foreach ($reflectionMethodConstructor->getParameters() as $index => $reflectionParameter) {
                    if (is_string($key) && mb_strtolower($key) === mb_strtolower($reflectionParameter->getName())) {
                        $reflectionParameterMatchingKey = $reflectionParameter;

                        break;
                    }
                }

                $isSensitive = (
                    $reflectionParameterMatchingKey
                    && ($reflectionParameterMatchingKey->getAttributes(SensitiveParameter::class)[0] ?? false)
                );
            }

            if ($isSensitive) {
                $argumentsAsStrings[] = sprintf(
                    '%s: %s',
                    $key,
                    $caster->getSensitiveMessage(),
                );
            } else {
                $argumentsAsStrings[] = sprintf(
                    '%s: %s',
                    $key,
                    $caster->cast($value),
                );
            }
        }

        if ($argumentsAsStrings) {
            $str .= sprintf(' (%s)', implode(', ', $argumentsAsStrings));
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

    public function isHandling(object $object): bool
    {
        return ($object instanceof ReflectionAttribute);
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
