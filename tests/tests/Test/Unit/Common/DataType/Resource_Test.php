<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType;

use Closure;
use Eboreum\Caster\Common\DataType\Resource_;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function implode;

#[CoversClass(Resource_::class)]
class Resource_Test extends TestCase // phpcs:ignore
{
    /**
     * @return array<int, array{0: mixed, 1: callable|null}>
     */
    public static function providerTestBasics(): array
    {
        return [
            [
                fopen(__FILE__, 'r'),
                static function (Resource_ $resource): void {
                    fclose($resource->getResource());
                },
            ],
        ];
    }

    /**
     * @param resource $resource
     */
    #[DataProvider('providerTestBasics')]
    public function testBasics($resource, ?Closure $takeDownCallback): void
    {
        $resourceObject = new Resource_($resource);

        $this->assertSame($resource, $resourceObject->getResource());

        if ($takeDownCallback) {
            $takeDownCallback($resourceObject);
        }
    }

    public function testConstructorThrowsExceptionWhenArgumentResourceIsInvalid(): void
    {
        try {
            new Resource_(42); /** @phpstan-ignore-line Suppression code 03dec37a; see README.me */
        } catch (Exception $e) {
            $exceptionCurrent = $e;
            $this->assertSame('InvalidArgumentException', $exceptionCurrent::class);
            $this->assertMatchesRegularExpression(
                implode('', [
                    '/',
                    '^',
                    'Expects argument \$resource to be a resource, but it is not\.',
                    ' Found: \(int\) 42',
                    '$',
                    '/',
                ]),
                $exceptionCurrent->getMessage(),
            );

            $exceptionCurrent = $exceptionCurrent->getPrevious();
            $this->assertTrue(null === $exceptionCurrent);

            return;
        }

        $this->fail('Exception was never thrown.');
    }
}
