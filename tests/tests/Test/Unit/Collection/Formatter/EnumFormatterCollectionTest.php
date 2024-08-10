<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Collection\Formatter\EnumFormatterCollection;
use Eboreum\Caster\Contract\Formatter\EnumFormatterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumFormatterCollection::class)]
class EnumFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(EnumFormatterInterface::class, EnumFormatterCollection::getHandledClassName());
    }
}
