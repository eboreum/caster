<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\Object_\PublicVariableFormatter;
use PHPUnit\Framework\TestCase;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\PublicVariableFormatterTest\testFormatWorksWhenObjectHasMultipleSameNamePublicVariables; // phpcs:ignore

class PublicVariableFormatterTest extends TestCase
{
    public function testIsSkippedWhenObjectHasNoPublicVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();
        $object = new \stdClass();

        $this->assertFalse($publicVariableFormatter->isHandling($object));
        $this->assertNull($publicVariableFormatter->format($caster, $object));
    }

    public function testFormatWorksWhenObjectHasOnePublicInjectedVariable(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();
        $object = new \stdClass();
        $object->foo = 'bar';

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $this->assertSame(
            '\stdClass {$foo = "bar"}',
            $publicVariableFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWhenObjectHasMultiplePublicInjectedVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();

        $object = new \stdClass();
        $object->foo = 1;
        $object->bar = null;
        $object->baz = 'hmm';

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $this->assertSame(
            '\stdClass {$foo = 1, $bar = null, $baz = "hmm"}',
            $publicVariableFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWhenObjectHasOnePublicVariable(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();

        $object = new class
        {
            public string $foo = 'bar';
        };

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $formatted = $publicVariableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s:\d+ \{',
                        '\$foo = "bar"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWhenObjectHasMultiplePublicVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();

        $object = new class
        {
            public int $foo = 1;
            public ?string $bar = null;
            public string $baz = 'hmm';
            protected ?string $protected = null;
            private ?string $private = null; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
        };

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $formatted = $publicVariableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s:\d+ \{',
                        '\$foo = 1',
                        ', \$bar = null',
                        ', \$baz = "hmm"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWhenObjectHasMultipleSameNamePublicVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();

        $object = new testFormatWorksWhenObjectHasMultipleSameNamePublicVariables\ClassA();

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $formatted = $publicVariableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{',
                        '\$foo = "a"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(testFormatWorksWhenObjectHasMultipleSameNamePublicVariables\ClassA::class, '/'),
            ),
            $formatted,
        );
    }
}
