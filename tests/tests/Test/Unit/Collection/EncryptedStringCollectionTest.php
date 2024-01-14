<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection;

use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\EncryptedString;
use PHPUnit\Framework\TestCase;

/**
 * {@inheritDoc}
 *
 * @covers \Eboreum\Caster\Collection\EncryptedStringCollection
 */
class EncryptedStringCollectionTest extends TestCase
{
    public function testGetHandledClassNameWorks(): void
    {
        $this->assertSame(EncryptedString::class, EncryptedStringCollection::getHandledClassName());
    }
}
