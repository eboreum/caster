<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function implode;
use function is_object;
use function json_encode;
use function preg_quote;
use function sprintf;

use const PHP_INT_MAX;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Common\DataType\Integer\PositiveInteger
 */
class PositiveIntegerTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestBasics
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
    public function dataProviderTestBasics(): array
    {
        return [
            [1],
            [42],
            [PHP_INT_MAX],
        ];
    }

    public function testConstructorThrowsExceptionWhenArgumentIntegerIsOutOfBounds(): void
    {
        try {
            new PositiveInteger(0);
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
                    preg_quote(PositiveInteger::class, '/'),
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
