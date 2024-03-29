<?php

declare(strict_types=1);

namespace Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\DataType\Integer\IntegerInterface;
use Eboreum\Caster\Exception\RuntimeException;
use ReflectionObject;
use Throwable;

use function implode;
use function is_int;
use function sprintf;
use function strval;

abstract class AbstractInteger implements IntegerInterface
{
    public static function getMaximumLimit(): ?int
    {
        return null;
    }

    public static function getMinimumLimit(): ?int
    {
        return null;
    }

    #[DebugIdentifier]
    protected int $integer;

    /**
     * @throws RuntimeException
     */
    public function __construct(int $integer)
    {
        try {
            $errorMessages = [];

            if (is_int(static::getMinimumLimit())) {
                if (false === ($integer >= static::getMinimumLimit())) {
                    $errorMessages[] = sprintf(
                        'Argument $integer must be >= the minimum limit of %d, but it is not. Found: %s',
                        static::getMinimumLimit(),
                        Caster::getInternalInstance()->castTyped($integer),
                    );
                }
            }

            if (is_int(static::getMaximumLimit())) {
                if (false === ($integer <= static::getMaximumLimit())) {
                    $errorMessages[] = sprintf(
                        'Argument $integer must be <= the maximum limit of %d, but it is not. Found: %s',
                        static::getMaximumLimit(),
                        Caster::getInternalInstance()->castTyped($integer),
                    );
                }
            }

            if ($errorMessages) {
                throw new RuntimeException(implode('. ', $errorMessages));
            }

            $this->integer = $integer;
        } catch (Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$integer = %s',
                Caster::create()->castTyped($integer),
            );

            throw new RuntimeException(sprintf(
                'Failed to construct %s with arguments {%s}',
                Caster::makeNormalizedClassName(new ReflectionObject($this)),
                implode(', ', $argumentsAsStrings),
            ), 0, $t);
        }
    }

    public function jsonSerialize(): string
    {
        return strval($this->integer);
    }

    public function toInteger(): int
    {
        return $this->integer;
    }

    public function isSame(IntegerInterface $integer): bool
    {
        return $this->toInteger() === $integer->toInteger();
    }
}
