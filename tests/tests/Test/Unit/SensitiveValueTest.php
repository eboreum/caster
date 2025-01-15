<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\SensitiveValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[CoversClass(SensitiveValue::class)]
class SensitiveValueTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testGetInstanceWorks(): void
    {
        $a = SensitiveValue::getInstance();
        $b = SensitiveValue::getInstance();

        $this->assertSame($a, $b);
    }
}
