<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectFormatterCollection::class)]
class ObjectFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(ObjectFormatterInterface::class, ObjectFormatterCollection::getHandledClassName());
    }
}
