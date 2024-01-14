<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType\Integer;

use Eboreum\Caster\Common\DataType\Integer\AbstractInteger;
use Eboreum\Caster\Common\DataType\Integer\NegativeInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * {@inheritDoc}
 *
 * @covers Eboreum\Caster\Common\DataType\Integer\AbstractInteger
 */
class AbstractIntegerTest extends TestCase
{
    public function testGetMaximumLimitWorks(): void
    {
        $this->assertNull(AbstractInteger::getMaximumLimit());
    }

    public function testGetMinimumLimitWorks(): void
    {
        $this->assertNull(AbstractInteger::getMinimumLimit());
    }

    public function testConstructorThrowsExceptionWhenMinimumLimitIsNotAdheredTo(): void
    {
        try {
            new UnsignedInteger(-1);
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                sprintf(
                    'Failed to construct \\%s with arguments {$integer = (int) -1}',
                    UnsignedInteger::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException)); // Make phpstan happy
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                'Argument $integer must be >= the minimum limit of 0, but it is not. Found: (int) -1',
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testConstructorThrowsExceptionWhenMaximumLimitIsNotAdheredTo(): void
    {
        try {
            new NegativeInteger(1);
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                sprintf(
                    'Failed to construct \\%s with arguments {$integer = (int) 1}',
                    NegativeInteger::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertIsObject($currentException);
            assert(is_object($currentException)); // Make phpstan happy
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertSame(
                'Argument $integer must be <= the maximum limit of -1, but it is not. Found: (int) 1',
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }
}
