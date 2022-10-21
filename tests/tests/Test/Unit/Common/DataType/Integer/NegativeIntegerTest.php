<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\NegativeInteger;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function implode;
use function is_object;
use function json_encode;
use function preg_quote;
use function sprintf;

use const PHP_INT_MIN;

class NegativeIntegerTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestBasics
     */
    public function testBasics(int $integer): void
    {
        $negativeIntegerA = new NegativeInteger($integer);

        $this->assertSame(json_encode($integer), $negativeIntegerA->jsonSerialize());
        $this->assertSame($integer, $negativeIntegerA->toInteger());
        $this->assertTrue($negativeIntegerA->isSame($negativeIntegerA));

        $negativeIntegerB = new NegativeInteger($integer);

        $this->assertTrue($negativeIntegerA->isSame($negativeIntegerB));

        $negativeIntegerC = new NegativeInteger(-2);

        $this->assertFalse($negativeIntegerA->isSame($negativeIntegerC));
    }

    /**
     * @return array<int, array{0: int}>
     */
    public function dataProviderTestBasics(): array
    {
        return [
            [
                -1,
            ],
            [
                -42,
            ],
            [PHP_INT_MIN],
        ];
    }

    public function testConstructorThrowsExceptionWhenArgumentIntegerIsOutOfBounds(): void
    {
        try {
            new NegativeInteger(0);
        } catch (Exception $e) {
            $exceptionCurrent = $e;
            $this->assertSame(RuntimeException::class, $exceptionCurrent::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failed to construct \\\\%s with arguments \{',
                            '\$integer = \(int\) 0',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(NegativeInteger::class, '/'),
                ),
                $exceptionCurrent->getMessage(),
            );

            $exceptionCurrent = $exceptionCurrent->getPrevious();
            $this->assertIsObject($exceptionCurrent);
            assert(is_object($exceptionCurrent)); // Make phpstan happy
            $this->assertSame(RuntimeException::class, $exceptionCurrent::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Argument \$integer must be \<= the maximum limit of %d, but it is not\.',
                        ' Found: \(int\) 0',
                        '$',
                        '/',
                    ]),
                    NegativeInteger::getMaximumLimit(),
                ),
                $exceptionCurrent->getMessage(),
            );

            $exceptionCurrent = $exceptionCurrent->getPrevious();
            $this->assertTrue(null === $exceptionCurrent);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testGetMaximumLimitWorks(): void
    {
        $this->assertIsInt(NegativeInteger::getMaximumLimit());
    }

    public function testGetMinimumLimitWorks(): void
    {
        $this->assertNull(NegativeInteger::getMinimumLimit());
    }
}
