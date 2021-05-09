<?php
declare(strict_types = 1);

namespace Eboreum\Caster\Test\Unit\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;
use PHPUnit\Framework\TestCase;

class TextuallyIdentifiableInterfaceFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $textuallyIdentifiableInterfaceFormatter = new TextuallyIdentifiableInterfaceFormatter;
        $object = new \stdClass;

        $this->assertFalse($textuallyIdentifiableInterfaceFormatter->isHandling($object));
        $this->assertNull($textuallyIdentifiableInterfaceFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $textuallyIdentifiableInterfaceFormatter = new TextuallyIdentifiableInterfaceFormatter;

        $object = new class implements TextuallyIdentifiableInterface
        {
            private ?int $id = null;

            public function __construct()
            {
                $this->id = 22;
            }

            public function toTextualIdentifier(CasterInterface $caster): string
            {
                return sprintf(
                    "%s {\$id = %s}",
                    Caster::makeNormalizedClassName(new \ReflectionObject($this)),
                    $caster->cast($this->id),
                );
            }
        };

        $this->assertTrue($textuallyIdentifiableInterfaceFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            implode("", [
                '/',
                '^',
                'class@anonymous\/in\/.+\/TextuallyIdentifiableInterfaceFormatterTest\.php:\d+ \{',
                    '\$id = 22',
                '\}',
                '$',
                '/',
            ]),
            $textuallyIdentifiableInterfaceFormatter->format($caster, $object),
        );
    }
}
