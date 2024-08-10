<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayFormatterCollection::class)]
class ArrayFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(ArrayFormatterInterface::class, ArrayFormatterCollection::getHandledClassName());
    }
}
