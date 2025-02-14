<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\DebugInfoFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function basename;
use function implode;
use function preg_quote;
use function sprintf;

#[CoversClass(DebugInfoFormatter::class)]
class DebugInfoFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $debugInfoFormatter = new DebugInfoFormatter();

        $object = new class
        {
        };

        $this->assertFalse($debugInfoFormatter->isHandling($object));
        $this->assertNull($debugInfoFormatter->format($caster, $object));
    }

    public function testFormatWorks(): void
    {
        $caster = Caster::create();
        $debugInfoFormatter = new DebugInfoFormatter();

        $object = new class
        {
            /**
             * @return array<string, string>
             */
            public function __debugInfo(): array
            {
                return ['foo' => 'bar'];
            }
        };

        $this->assertTrue($debugInfoFormatter->isHandling($object));
        $formatted = $debugInfoFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s\:\d+ \(\[',
                        '"foo" \=\> "bar"',
                    '\]\)',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            $formatted,
        );
    }
}
