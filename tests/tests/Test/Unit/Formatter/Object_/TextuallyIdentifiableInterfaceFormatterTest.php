<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;
use PHPUnit\Framework\TestCase;

class TextuallyIdentifiableInterfaceFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $textuallyIdentifiableInterfaceFormatter = new TextuallyIdentifiableInterfaceFormatter();
        $object = new \stdClass();

        $this->assertFalse($textuallyIdentifiableInterfaceFormatter->isHandling($object));
        $this->assertNull($textuallyIdentifiableInterfaceFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $textuallyIdentifiableInterfaceFormatter = new TextuallyIdentifiableInterfaceFormatter();

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
                    '{$id = %s}',
                    $caster->cast($this->id),
                );
            }
        };

        $this->assertTrue($textuallyIdentifiableInterfaceFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s\:\d+\: \{',
                        '\$id = 22',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            $textuallyIdentifiableInterfaceFormatter->format($caster, $object),
        );
    }
}
