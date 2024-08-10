<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Contract\Formatter\StringFormatterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringFormatterCollection::class)]
class StringFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(StringFormatterInterface::class, StringFormatterCollection::getHandledClassName());
    }
}
