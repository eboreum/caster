<?php

declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster\Common\DataType;

use Eboreum\Caster\Common\DataType\Resource;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
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
        $resourceObject = new Resource($resource);

        $this->assertSame($resource, $resourceObject->getResource());

        if ($takeDownCallback) {
            $takeDownCallback($resourceObject);
        }
    }

    public function dataProvider_testBasics(): array
    {
        return [
            [
                \xml_parser_create("UTF-8"),
                null,
            ],
            [
                \fopen(__FILE__, "r"),
                function(Resource $resource){
                    fclose($resource->getResource());
                },
            ],
        ];
    }

    public function testConstructorThrowsExceptionWhenArgumentResourceIsInvalid(): void
    {
        try {
            new Resource(42);
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
