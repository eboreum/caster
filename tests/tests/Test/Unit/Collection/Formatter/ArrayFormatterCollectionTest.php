<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection;

use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;
use PHPUnit\Framework\TestCase;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection
 */
class ArrayFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(ArrayFormatterInterface::class, ArrayFormatterCollection::getHandledClassName());
    }
}
