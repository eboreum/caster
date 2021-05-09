<?php

declare(strict_types = 1);

namespace Eboreum\Caster;

use Eboreum\Caster\Contract\CharacterEncodingInterface;
use Eboreum\Caster\Exception\RuntimeException;

class CharacterEncoding implements CharacterEncodingInterface
{
    private static ?CharacterEncoding $instance = null;

    protected string $name;

    /**
     * @throws RuntimeException
     */
    public function __construct(string $name)
    {
        try {
            if (false === static::isCharacterEncodingValid($name)) {
                throw new RuntimeException(sprintf(
                    implode(
                        "",
                        [
                            "Argument \$name is not a valid character encoding.",
                            " Expected it to be one of: [%s], but it is not.",
                            " Found: (string(%d)) \"%s\"",
                        ],
                    ),
                    implode(
                        ", ",
                        array_map(
                            function(string $name){
                                return escapeshellarg($name);
                            },
                            mb_list_encodings(),
                        ),
                    ),
                    mb_strlen($name),
                    addcslashes($name, "\\\""),
                ));
            }

            $this->name = $name;
        } catch (\Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                "\$name = %s",
                Caster::getInternalInstance()->castTyped($name),
            );

            throw new RuntimeException(sprintf(
                "Failed to construct %s with arguments {%s}",
                Caster::makeNormalizedClassName(new \ReflectionObject($this)),
                implode(", ", $argumentsAsStrings),
            ), 0, $t);
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function isSame(CharacterEncodingInterface $characterEncoding): bool
    {
        return $this->getName() === $characterEncoding->getName();
    }

    /**
     * {@inheritDoc}
     */
    public static function isCharacterEncodingValid(string $name): bool
    {
        return in_array(
            $name,
            mb_list_encodings(),
            true,
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getInstance(): CharacterEncoding
    {
        if (null === self::$instance) {
            self::$instance = new self(mb_internal_encoding());
        }

        return self::$instance;
    }
}
