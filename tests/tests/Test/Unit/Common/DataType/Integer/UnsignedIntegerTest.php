<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
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
 * @covers Eboreum\Caster\Common\DataType\Integer\AbstractInteger
 * @covers Eboreum\Caster\Common\DataType\Integer\UnsignedInteger
 */
class UnsignedIntegerTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestBasics
     */
    public function testBasics(int $integer): void
    {
        $unsignedIntegerA = new UnsignedInteger($integer);

        $this->assertSame(json_encode($integer), $unsignedIntegerA->jsonSerialize());
        $this->assertSame($integer, $unsignedIntegerA->toInteger());
        $this->assertTrue($unsignedIntegerA->isSame($unsignedIntegerA));

        $unsignedIntegerB = new UnsignedInteger($integer);

        $this->assertTrue($unsignedIntegerA->isSame($unsignedIntegerB));

        $unsignedIntegerC = new UnsignedInteger(2);

        $this->assertFalse($unsignedIntegerA->isSame($unsignedIntegerC));
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
            new UnsignedInteger(-1);
        } catch (Exception $e) {
            $exceptionCurrent = $e;
            $this->assertSame(RuntimeException::class, $exceptionCurrent::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failed to construct \\\\%s with arguments \{',
                            '\$integer = \(int\) -1',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(UnsignedInteger::class, '/'),
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
                        ' Found: \(int\) -1',
                        '$',
                        '/',
                    ]),
                    UnsignedInteger::getMinimumLimit(),
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
        $this->assertNull(UnsignedInteger::getMaximumLimit());
    }

    public function testGetMinimumLimitWorks(): void
    {
        $this->assertIsInt(UnsignedInteger::getMinimumLimit());
    }
}
