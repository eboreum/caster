<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Caster;

use Eboreum\Caster\Caster\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    public function testBasics(): void
    {
        $contextA = new Context();
        $object = new stdClass();

        $this->assertCount(0, $contextA);
        $this->assertTrue($contextA->isEmpty());
        $this->assertFalse($contextA->hasVisitedObject($object));

        $contextB = $contextA->withAddedVisitedObject($object);

        $this->assertCount(1, $contextB);
        $this->assertFalse($contextB->isEmpty());
        $this->assertTrue($contextB->hasVisitedObject($object));
    }

    public function testWithAddedVisitedObjectWorks(): void
    {
        $contextA = new Context();
        $object = new stdClass();

        $this->assertFalse($contextA->hasVisitedObject($object));

        $contextB = $contextA->withAddedVisitedObject($object);

        $this->assertNotSame($contextA, $contextB);
        $this->assertTrue($contextB->hasVisitedObject($object));
    }
}
