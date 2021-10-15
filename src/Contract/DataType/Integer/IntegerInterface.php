<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract\DataType\Integer;

use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;
use Eboreum\Caster\Contract\ImmutableObjectInterface;

/**
 * Denotes that the implementing class contains an integer of some special sort.
 */
interface IntegerInterface extends ImmutableObjectInterface, DebugIdentifierAnnotationInterface, \JsonSerializable
{
    /**
     * Must return the integer as a string to avoid floating point weirdness, both in PHP
     * (PHP_INT_MAX + PHP_INT_MAX will result in a float value) and elsewhere, e.g. in Javascript, which doesn't have
     * an "int" type, but only "number".
     */
    public function jsonSerialize(): string;

    /**
     * Must return the integer contained in the implementing class "as is".
     */
    public function toInteger(): int;

    /**
     * Compare two integers and return `true` if they are identical.
     */
    public function isSame(IntegerInterface $integer): bool;

    /**
     * The value the integer must be less than or equal to when the implementing class is being constructed or cloned.
     * When `null`, it means no limit must be imposed, although PHP_INT_MAX is implicit.
     */
    public static function getMaximumLimit(): ?int;

    /**
     * The value the integer must be greater than or equal to when the implementing class is being constructed or
     * cloned.
     * When `null`, it means no limit must be imposed, although PHP_INT_MIN is implicit.
     */
    public static function getMinimumLimit(): ?int;
}
