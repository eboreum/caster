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

#[CoversClass('Eboreum\Caster\Functions')]
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
        $this->assertFalse(Functions::is_enum(null));
        $this->assertFalse(Functions::is_enum(true));
        $this->assertFalse(Functions::is_enum(42));
        $this->assertFalse(Functions::is_enum(3.14));
        $this->assertFalse(Functions::is_enum('foo'));
        $this->assertFalse(Functions::is_enum([]));
        $this->assertFalse(Functions::is_enum(new stdClass()));
        $this->assertTrue(Functions::is_enum(FooEnum::Lorem));
        $this->assertTrue(Functions::is_enum(FooEnum::Ipsum));
        $this->assertTrue(Functions::is_enum(FooEnum::from('Lorem')));
        $this->assertFalse(Functions::is_enum(FooEnum::tryFrom('foo')));
        $this->assertFalse(Functions::is_enum(FooEnum::class));
    }
}
