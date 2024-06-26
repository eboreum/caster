<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract;

/**
 * @inheritDoc
 *
 * Implementing class must contain a guaranteed to be valid character encoding.
 *
 *   - `mb_list_encodings`: https://www.php.net/manual/en/function.mb-list-encodings.php
 *   - `mb_internal_encoding`: https://www.php.net/manual/en/function.mb-internal-encoding.php
 */
interface CharacterEncodingInterface extends ImmutableObjectInterface
{
    /**
     * Must always return the same instance.
     */
    public static function getInstance(): CharacterEncodingInterface;

    /**
     * Must determine whether the given character encoding by name is valid on the current system.
     */
    public static function isCharacterEncodingValid(string $name): bool;

    public function __toString(): string;

    public function getName(): string;

    /**
     * Must return true when two character encodings are considered to be the same. Otherwise, must return false.
     */
    public function isSame(CharacterEncodingInterface $characterEncoding): bool;
}
