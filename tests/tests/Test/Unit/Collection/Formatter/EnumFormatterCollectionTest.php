<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection;

use Eboreum\Caster\Collection\Formatter\EnumFormatterCollection;
use Eboreum\Caster\Contract\Formatter\EnumFormatterInterface;
use PHPUnit\Framework\TestCase;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Collection\Formatter\EnumFormatterCollection
 */
class EnumFormatterCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(EnumFormatterInterface::class, EnumFormatterCollection::getHandledClassName());
    }
}
