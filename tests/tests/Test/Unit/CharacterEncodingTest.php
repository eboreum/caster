<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class CharacterEncodingTest extends TestCase
{
    public function testBasics(): void
    {
        $characterEncoding = new CharacterEncoding(mb_internal_encoding());
        $this->assertSame(mb_internal_encoding(), (string)$characterEncoding);
        $this->assertSame(mb_internal_encoding(), $characterEncoding->getName());
    }

    public function testConstructorThrowsException(): void
    {
        try {
            new CharacterEncoding('5ffaf0ea-7520-4a09-b188-2a542e04d0f3');
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertSame(
                implode('', [
                    'Failed to construct \\Eboreum\\Caster\\CharacterEncoding with arguments {',
                        '$name = (string(36)) "5ffaf0ea-7520-4a09-b188-2a542e04d0f3"',
                    '}',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                implode('', [
                    '/',
                    '^',
                    'Argument \$name is not a valid character encoding\. Expected it to be one of: \[',
                        '\'[^\']+\'(, \'[^\']+\')*',
                    '\], but it is not\.',
                    ' Found: \(string\(36\)\) "5ffaf0ea-7520-4a09-b188-2a542e04d0f3"',
                    '$',
                    '/',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testIsCharacterEncodingValidWorks(): void
    {
        $this->assertTrue(CharacterEncoding::isCharacterEncodingValid(mb_internal_encoding()));
        $this->assertFalse(CharacterEncoding::isCharacterEncodingValid('5ffaf0ea-7520-4a09-b188-2a542e04d0f3'));
    }

    public function testGetInstanceWorks(): void
    {
        $this->assertSame(CharacterEncoding::getInstance(), CharacterEncoding::getInstance());
    }
}
