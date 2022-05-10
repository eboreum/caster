<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;

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

        assert($object instanceof \Closure); // Make phpstan happy

        $arguments = [];

        $reflectionFunction = new \ReflectionFunction($object);

        foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
            $argument = sprintf(
                '$%s',
                $reflectionParameter->getName(),
            );

            if ($reflectionParameter->isPassedByReference()) {
                $argument = '&' . $argument;
            }

            if ($reflectionParameter->isVariadic()) {
                $argument = '...' . $argument;
            }

            $reflectionType = $reflectionParameter->getType();

            if ($reflectionType) {
                $typeText = (string)$reflectionType;

                if ($reflectionParameter->isDefaultValueAvailable()) {
                    if ($reflectionParameter->isDefaultValueConstant()) {
                        $constantName = $reflectionParameter->getDefaultValueConstantName();

                        if ($constantName !== null && preg_match('/\\\\/', $constantName)) {
                            $constantName = sprintf(
                                '\\%s',
                                $constantName,
                            );
                        }

                        $argument .= ' = ' . $constantName;
                    } else {
                        $argument .= ' = ' . $caster->cast($reflectionParameter->getDefaultValue());
                    }
                }

                $argument = sprintf(
                    '%s %s',
                    $typeText,
                    $argument,
                );
            }

            $arguments[] = $argument;
        }

        $reflectionReturnType = $reflectionFunction->getReturnType();

        $return = sprintf(
            '%s(%s)',
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            implode(', ', $arguments)
        );

        if ($reflectionReturnType) {
            $return .= sprintf(
                ': %s',
                (string)$reflectionReturnType,
            );
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return $object instanceof \Closure;
    }
}
