<?php

declare(strict_types=1);

namespace Eboreum\Caster\Common\DataType\String_;

use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\CharacterEncodingInterface;
use Eboreum\Caster\Contract\CharacterInterface;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;
use Eboreum\Caster\Exception\RuntimeException;

/**
 * Contains a single character. No more. No less.
 */
class Character implements CharacterInterface, DebugIdentifierAttributeInterface
{
    #[DebugIdentifier]
    protected string $character;

    #[DebugIdentifier]
    protected CharacterEncodingInterface $characterEncoding;

    /**
     * @throws RuntimeException
     */
    public function __construct(string $character, ?CharacterEncodingInterface $characterEncoding = null)
    {
        try {
            $characterEncodingVariant = $characterEncoding;

            if (null === $characterEncodingVariant) {
                $characterEncodingVariant = CharacterEncoding::getInstance();
            }

            if (1 !== mb_strlen($character, (string)$characterEncodingVariant)) {
                throw new RuntimeException(sprintf(
                    implode('', [
                        'Argument $character must be exactly 1 character, using character encoding %s, but it is not.',
                        ' Found: %s',
                    ]),
                    Caster::getInternalInstance()->cast($characterEncodingVariant),
                    Caster::getInternalInstance()->castTyped($character),
                ));
            }

            $this->character = $character;
            $this->characterEncoding = $characterEncodingVariant;
        } catch (\Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$character = %s',
                Caster::create()->castTyped($character),
            );
            $argumentsAsStrings[] = sprintf(
                '$characterEncoding = %s',
                Caster::create()->castTyped($characterEncoding),
            );

            throw new RuntimeException(sprintf(
                'Failed to construct %s with arguments {%s}',
                Caster::makeNormalizedClassName(new \ReflectionObject($this)),
                implode(', ', $argumentsAsStrings),
            ), 0, $t);
        }
    }

    public function __toString(): string
    {
        return $this->character;
    }

    /**
     * {@inheritDoc}
     */
    public function toTextualIdentifier(CasterInterface $caster): string
    {
        return sprintf(
            '%s {$character = %s, $characterEncoding = %s}',
            Caster::makeNormalizedClassName(new \ReflectionObject($this)),
            $caster->castTyped($this->character),
            $caster->castTyped($this->characterEncoding),
        );
    }

    public function getCharacter(): string
    {
        return $this->character;
    }

    public function getCharacterEncoding(): CharacterEncodingInterface
    {
        return $this->characterEncoding;
    }

    public function isSame(CharacterInterface $character): bool
    {
        return (
            (string)$this === (string)$character
            && $this->getCharacterEncoding()->isSame($character->getCharacterEncoding())
        );
    }
}
