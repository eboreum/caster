<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function implode;
use function is_object;
use function mb_internal_encoding;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\CharacterEncoding
 */
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
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                implode('', [
                    'Failed to construct \\Eboreum\\Caster\\CharacterEncoding with arguments {',
                        '$name = (string(36)) "5ffaf0ea-7520-4a09-b188-2a542e04d0f3"',
                    '}',
                ]),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException)); // Make phpstan happy
            $this->assertSame(RuntimeException::class, $currentException::class);
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

    public function testIsSameWorks(): void
    {
        $this->assertTrue(CharacterEncoding::getInstance()->isSame(CharacterEncoding::getInstance()));
        $this->assertTrue((new CharacterEncoding('UTF-8'))->isSame(new CharacterEncoding('UTF-8')));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetInstanceWorks(): void
    {
        $this->assertSame(CharacterEncoding::getInstance(), CharacterEncoding::getInstance());
    }
}
