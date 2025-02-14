<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\AbstractInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function implode;
use function json_encode;
use function preg_quote;
use function sprintf;

use const PHP_INT_MAX;

#[CoversClass(AbstractInteger::class)]
#[CoversClass(UnsignedInteger::class)]
class UnsignedIntegerTest extends TestCase
{
    /**
     * @return array<int, array{0: int}>
     */
    public static function providerTestBasics(): array
    {
        return [
            [1],
            [42],
            [PHP_INT_MAX],
        ];
    }

    #[DataProvider('providerTestBasics')]
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
        $this->assertSame(0, UnsignedInteger::getMinimumLimit());
    }
}
