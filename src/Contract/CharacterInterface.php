<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract;

/**
 * @inheritDoc
 *
 * Implementing class must contain a single character for the respective encoding. Allows multibyte characters.
 *
 *   - `mb_strlen`: https://www.php.net/manual/en/function.mb-strlen.php
 */
interface CharacterInterface extends ImmutableObjectInterface, TextuallyIdentifiableInterface
{
    public function __toString(): string;

    public function getCharacter(): string;

    public function getCharacterEncoding(): CharacterEncodingInterface;

    /**
     * Must return true when two characters are considered to be the same. Otherwise, must return false.
     * Must also check if character encodings are identical.
     */
    public function isSame(CharacterInterface $character): bool;
}
