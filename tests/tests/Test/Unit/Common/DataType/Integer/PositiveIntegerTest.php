<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class PositiveIntegerTest extends TestCase
{
    /**
     * @dataProvider dataProvider_testBasics
     */
    public function testBasics(int $integer): void
    {
        $positiveIntegerA = new PositiveInteger($integer);

        $this->assertSame(json_encode($integer), $positiveIntegerA->jsonSerialize());
        $this->assertSame($integer, $positiveIntegerA->toInteger());
        $this->assertTrue($positiveIntegerA->isSame($positiveIntegerA));

        $positiveIntegerB = new PositiveInteger($integer);

        $this->assertTrue($positiveIntegerA->isSame($positiveIntegerB));

        $positiveIntegerC = new PositiveInteger(2);

        $this->assertFalse($positiveIntegerA->isSame($positiveIntegerC));
    }

    /**
     * @return array<int, array{0: int}>
     */
    public function dataProvider_testBasics(): array
    {
        return [
            [
                1,
            ],
            [
                42,
            ],
            [
                PHP_INT_MAX,
            ],
        ];
    }

    public function testConstructorThrowsExceptionWhenArgumentIntegerIsOutOfBounds(): void
    {
        try {
            new PositiveInteger(0);
        } catch (\Exception $e) {
            $exceptionCurrent = $e;
            $this->assertSame(RuntimeException::class, get_class($exceptionCurrent));
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
                    preg_quote(PositiveInteger::class, '/'),
                ),
                $exceptionCurrent->getMessage(),
            );

            $exceptionCurrent = $exceptionCurrent->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($exceptionCurrent));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Argument \$integer must be \>= the minimum limit of %d, but it is not\.',
                        ' Found: \(int\) 0',
                        '$',
                        '/',
                    ]),
                    PositiveInteger::getMinimumLimit(),
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
        $this->assertNull(PositiveInteger::getMaximumLimit());
    }

    public function testGetMinimumLimitWorks(): void
    {
        $this->assertIsInt(PositiveInteger::getMinimumLimit());
    }
}
