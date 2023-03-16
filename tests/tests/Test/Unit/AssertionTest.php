<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\Assertion;
use Eboreum\Caster\Exception\AssertionException;
use Exception;
use PHPUnit\Framework\TestCase;

class AssertionTest extends TestCase
{
    public function testAssertIsStringWorks(): void
    {
        Assertion::assertIsString('');
        Assertion::assertIsString('foo');

        $this->assertTrue(true);
    }

    /**
     * @dataProvider dataProviderTestAssertIsStringThrowsExceptionWhenValueIsNotAString
     */
    public function testAssertIsStringThrowsExceptionWhenValueIsNotAString(
        string $expected,
        mixed $value,
        ?string $message
    ): void {
        try {
            Assertion::assertIsString($value, $message);
        } catch (Exception $e) {
            $currentException = $e;
            $this->assertSame(AssertionException::class, $currentException::class);
            $this->assertSame($expected, $currentException->getMessage());

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    /**
     * @return array<array{string, mixed, string|null}>
     */
    public function dataProviderTestAssertIsStringThrowsExceptionWhenValueIsNotAString(): array
    {
        return [
            [
                'Expects argument $value = (int) 42 to be a string, but it is not',
                42,
                null,
            ],
            [
                'Expects argument $value = (bool) true to be a string, but it is not: Lorem ipsum',
                true,
                'Lorem ipsum',
            ],
        ];
    }
}
