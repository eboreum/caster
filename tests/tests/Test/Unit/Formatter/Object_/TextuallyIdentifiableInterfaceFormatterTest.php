<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\TextuallyIdentifiableInterface;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

use function assert;
use function basename;
use function implode;
use function is_string;
use function preg_quote;
use function sprintf;

#[CoversClass(TextuallyIdentifiableInterfaceFormatter::class)]
class TextuallyIdentifiableInterfaceFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $textuallyIdentifiableInterfaceFormatter = new TextuallyIdentifiableInterfaceFormatter();
        $object = new stdClass();

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
        $formatted = $textuallyIdentifiableInterfaceFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
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
            $formatted,
        );
    }
}
