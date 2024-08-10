<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection\Formatter;

use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceFormatterCollection::class)]
class ResourceFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(ResourceFormatterInterface::class, ResourceFormatterCollection::getHandledClassName());
    }
}
