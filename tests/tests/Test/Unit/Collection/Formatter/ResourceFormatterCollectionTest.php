<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection;

use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;
use PHPUnit\Framework\TestCase;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection
 */
class ResourceFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(ResourceFormatterInterface::class, ResourceFormatterCollection::getHandledClassName());
    }
}
