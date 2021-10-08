<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Caster;

/**
 * Formatter for \Closure.
 */
class ClosureFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof \Closure);

        $arguments = [];

        $reflectionFunction = new \ReflectionFunction($object);

        foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
            $argument = "\${$reflectionParameter->getName()}";

            if ($reflectionParameter->isPassedByReference()) {
                $argument = "&" . $argument;
            }

            if ($reflectionParameter->isVariadic()) {
                $argument = "..." . $argument;
            }

            if ($reflectionParameter->hasType()) {
                $reflectionType = $reflectionParameter->getType();

                assert($reflectionType instanceof \ReflectionNamedType);

                $typeText = $reflectionType->getName();

                if (class_exists($typeText)) {
                    $typeText = "\\{$typeText}";
                }

                if ($reflectionParameter->allowsNull()) {
                    $typeText = "?" . $typeText;
                }

                if ($reflectionParameter->isDefaultValueAvailable()) {
                    if ($reflectionParameter->isDefaultValueConstant()) {
                        $constantName = $reflectionParameter->getDefaultValueConstantName();

                        if (preg_match('/\\\\/', $constantName)) {
                            $constantName = "\\{$constantName}";
                        }

                        $argument .= " = " . $constantName;
                    } else {
                        $argument .= " = " . $caster->cast($reflectionParameter->getDefaultValue());
                    }
                }

                $argument = "{$typeText} {$argument}";
            }

            $arguments[] = $argument;
        }

        return sprintf(
            "%s(%s)",
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            implode(", ", $arguments)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return $object instanceof \Closure;
    }
}
