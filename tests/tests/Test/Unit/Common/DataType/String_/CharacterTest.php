<?php

declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\String_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Common\DataType\String_\Character;
use Eboreum\Caster\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    public function testBasics(): void
    {
        $characterA = new Character("#");

        $this->assertSame("#", (string)$characterA);
        $this->assertSame(
            sprintf(
                implode("", [
                    "\\%s {",
                        "\$character = (string(1)) \"#\"",
                        ", \$characterEncoding = (object) \\%s",
                    "}",
                ]),
                Character::class,
                CharacterEncoding::class,
            ),
            $characterA->toTextualIdentifier(Caster::getInstance()),
        );
        $this->assertSame("#", $characterA->getCharacter());
        $this->assertSame(mb_internal_encoding(), (string)$characterA->getCharacterEncoding());

        $characterEncodingB = new CharacterEncoding(mb_internal_encoding());
        $characterB = new Character("#", $characterEncodingB);
        $this->assertSame("#", (string)$characterB);
        $this->assertSame(
            sprintf(
                implode("", [
                    "\\%s {",
                        "\$character = (string(1)) \"#\"",
                        ", \$characterEncoding = (object) \\%s",
                    "}",
                ]),
                Character::class,
                CharacterEncoding::class,
            ),
            $characterB->toTextualIdentifier(Caster::getInstance()),
        );
        $this->assertSame("#", $characterB->getCharacter());
        $this->assertSame($characterEncodingB, $characterB->getCharacterEncoding());

        $this->assertTrue($characterB->isSame($characterA));

        $characterC = new Character("?", $characterA->getCharacterEncoding());

        $this->assertFalse($characterC->isSame($characterA));

        $characterD = new Character("#", new CharacterEncoding("ISO-8859-1"));

        $this->assertFalse($characterD->isSame($characterA));
    }

    /**
     * @dataProvider dataProvider_testConstructorThrowsExceptionWhenArgumentCharacterIsInvalid
     */
    public function testConstructorThrowsExceptionWhenArgumentCharacterIsInvalid(
        string $message,
        string $expectedFailureCastString1,
        string $expectedFailureCastString2,
        string $string,
        ?CharacterEncoding $characterEncoding
    ): void
    {
        try {
            new Character($string, $characterEncoding);
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, get_class($currentException), $message);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Failed to construct \\\\%s with arguments \{',
                            '\$character = %s',
                            ', \$characterEncoding = %s',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(Character::class, "/"),
                    preg_quote($expectedFailureCastString1, "/"),
                    preg_quote($expectedFailureCastString2, "/"),
                ),
                $currentException->getMessage(),
                $message,
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($currentException), $message);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Argument \$character must be exactly 1 character, using character encoding \\\\%s',
                        ', but it is not\.',
                        ' Found: %s',
                        '$',
                        '/',
                    ]),
                    preg_quote(CharacterEncoding::class, "/"),
                    preg_quote($expectedFailureCastString1, "/"),
                ),
                $currentException->getMessage(),
                $message,
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(is_null($currentException), $message);

            return;
        }

        $this->fail("Exception was never thrown.");
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: null|CharacterEncoding}>
     */
    public function dataProvider_testConstructorThrowsExceptionWhenArgumentCharacterIsInvalid(): array
    {
        return [
            [
                "Empty string",
                "(string(0)) \"\"",
                "(null) null",
                "",
                null,
            ],
            [
                "More than 1 character",
                "(string(2)) \"ab\"",
                "(null) null",
                "ab",
                null,
            ],
            [
                "UTF-8 vs. ISO-8859-1",
                "(string(1)) \"æ\"",
                sprintf(
                    "(object) \\%s",
                    CharacterEncoding::class,
                ),
                "æ",
                new CharacterEncoding("ISO-8859-1"),
            ],
        ];
    }
}
