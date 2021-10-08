<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Common\DataType;

use Eboreum\Caster\Common\DataType\Resource_;
use PHPUnit\Framework\TestCase;

class Resource_Test extends TestCase
{
    /**
     * @dataProvider dataProvider_testBasics
     * @param resource $resource
     */
    public function testBasics(
        $resource,
        ?\Closure $takeDownCallback
    ): void
    {
        $resourceObject = new Resource_($resource);

        $this->assertSame($resource, $resourceObject->getResource());

        if ($takeDownCallback) {
            $takeDownCallback($resourceObject);
        }
    }

    /**
     * @return array<int, array{0: mixed, 1: null|callable}>
     */
    public function dataProvider_testBasics(): array
    {
        return [
            [
                \fopen(__FILE__, "r"),
                function(Resource_ $resource){
                    fclose($resource->getResource());
                },
            ],
        ];
    }

    public function testConstructorThrowsExceptionWhenArgumentResourceIsInvalid(): void
    {
        try {
            new Resource_(42); /** @phpstan-ignore-line */
        } catch (\Exception $e) {
            $exceptionCurrent = $e;
            $this->assertSame("InvalidArgumentException", get_class($exceptionCurrent));
            $this->assertMatchesRegularExpression(
                implode("", [
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
            $this->assertTrue(is_null($exceptionCurrent));

            return;
        }

        $this->fail("Exception was never thrown.");
    }
}
