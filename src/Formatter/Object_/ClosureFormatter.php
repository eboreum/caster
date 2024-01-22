<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Closure;
use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\WrappableInterface;
use ReflectionFunction;
use ReflectionObject;

use function array_walk;
use function assert;
use function count;
use function implode;
use function preg_match;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formatter for \Closure.
 */
class ClosureFormatter extends AbstractObjectFormatter implements WrappableInterface
{
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof Closure); // Make phpstan happy

        $arguments = [];

        $reflectionFunction = new ReflectionFunction($object);

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

        $isWrapping = $caster->isWrapping() && count($arguments) > 1;
        $delimiter = ', ';

        if ($isWrapping) {
            array_walk($arguments, static function (string &$argument) use ($caster): void {
                $argument = sprintf(
                    "\n%s%s",
                    $caster->getWrappingIndentationCharacters(),
                    $argument,
                );
            });

            $delimiter = ',';
        }

        $return = sprintf(
            '%s(%s%s)',
            Caster::makeNormalizedClassName(new ReflectionObject($object)),
            implode($delimiter, $arguments),
            ($isWrapping ? "\n" : ''),
        );

        if ($reflectionReturnType) {
            $return .= sprintf(
                ': %s',
                (string)$reflectionReturnType,
            );
        }

        return $return;
    }

    public function isHandling(object $object): bool
    {
        return $object instanceof Closure;
    }
}
