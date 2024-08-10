<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\Functions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use TestResource\Unit\Eboreum\Caster\functionsTest\testIsEnumWorks\FooEnum;

use function dirname;
use function in_array;

#[CoversClass(Functions::class)]
class FunctionsTest extends TestCase
{
    public function testRglobWorks(): void
    {
        $filePaths = Functions::rglob(dirname(TEST_ROOT_PATH) . '/src/*.php');

        $this->assertGreaterThan(0, $filePaths);

        $this->assertTrue(in_array(
            dirname(TEST_ROOT_PATH) . '/src/Caster.php',
            $filePaths,
            true,
        ));

        $this->assertTrue(in_array(
            dirname(TEST_ROOT_PATH) . '/src/Contract/ImmutableObjectInterface.php',
            $filePaths,
            true,
        ));
    }

    public function testIsEnumWorks(): void
    {
        $this->assertFalse(Functions::isEnum(null));
        $this->assertFalse(Functions::isEnum(true));
        $this->assertFalse(Functions::isEnum(42));
        $this->assertFalse(Functions::isEnum(3.14));
        $this->assertFalse(Functions::isEnum('foo'));
        $this->assertFalse(Functions::isEnum([]));
        $this->assertFalse(Functions::isEnum(new stdClass()));
        $this->assertTrue(Functions::isEnum(FooEnum::Lorem));
        $this->assertTrue(Functions::isEnum(FooEnum::Ipsum));
        $this->assertTrue(Functions::isEnum(FooEnum::from('Lorem')));
        $this->assertFalse(Functions::isEnum(FooEnum::tryFrom('foo')));
        $this->assertFalse(Functions::isEnum(FooEnum::class));
    }
}
