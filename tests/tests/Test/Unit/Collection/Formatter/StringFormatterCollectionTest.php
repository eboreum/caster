<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection;

use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Contract\Formatter\StringFormatterInterface;
use PHPUnit\Framework\TestCase;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Collection\Formatter\StringFormatterCollection
 */
class StringFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(StringFormatterInterface::class, StringFormatterCollection::getHandledClassName());
    }
}
