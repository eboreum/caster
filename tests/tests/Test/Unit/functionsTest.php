<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use PHPUnit\Framework\TestCase;
use TestResource\Unit\Eboreum\Caster\functionsTest\test_is_enum_works\FooEnum;

use function Eboreum\Caster\functions\is_enum;
use function Eboreum\Caster\functions\rglob;

class functionsTest extends TestCase
{
    public function test_rglob_works(): void
    {
        $filePaths = rglob(dirname(TEST_ROOT_PATH) . '/src/*.php');

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

    public function test_is_enum_works(): void
    {
        $this->assertFalse(is_enum(null));
        $this->assertFalse(is_enum(true));
        $this->assertFalse(is_enum(42));
        $this->assertFalse(is_enum(3.14));
        $this->assertFalse(is_enum('foo'));
        $this->assertFalse(is_enum([]));
        $this->assertFalse(is_enum(new \stdClass));
        $this->assertTrue(is_enum(FooEnum::Lorem));
        $this->assertTrue(is_enum(FooEnum::Ipsum));
        $this->assertTrue(is_enum(FooEnum::from('Lorem')));
        $this->assertFalse(is_enum(FooEnum::tryFrom('foo')));
        $this->assertFalse(is_enum(FooEnum::class));
    }
}
