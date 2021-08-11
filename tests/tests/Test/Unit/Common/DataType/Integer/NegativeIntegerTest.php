<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\NegativeInteger;
use Eboreum\Caster\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class NegativeIntegerTest extends TestCase
{
    /**
     * @dataProvider dataProvider_testBasics
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
    public function dataProvider_testBasics(): array
    {
        return [
            [
                -1,
            ],
            [
                -42,
            ],
            [
                PHP_INT_MIN,
            ],
        ];
    }

    public function testConstructorThrowsExceptionWhenArgumentIntegerIsOutOfBounds(): void
    {
        try {
            new NegativeInteger(0);
        } catch (\Exception $e) {
            $exceptionCurrent = $e;
            $this->assertSame(RuntimeException::class, get_class($exceptionCurrent));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Failed to construct \\\\%s with arguments \{',
                            '\$integer = \(int\) 0',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(NegativeInteger::class, "/"),
                ),
                $exceptionCurrent->getMessage(),
            );

            $exceptionCurrent = $exceptionCurrent->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($exceptionCurrent));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
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
            $this->assertTrue(is_null($exceptionCurrent));

            return;
        }

        $this->fail("Exception was never thrown.");
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
