<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\NegativeInteger;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function assert;
use function implode;
use function is_object;
use function json_encode;
use function preg_quote;
use function sprintf;

use const PHP_INT_MIN;

#[CoversClass(NegativeInteger::class)]
class NegativeIntegerTest extends TestCase
{
    /**
     * @return array<int, array{0: int}>
     */
    public static function providerTestBasics(): array
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

    #[DataProvider('providerTestBasics')]
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
