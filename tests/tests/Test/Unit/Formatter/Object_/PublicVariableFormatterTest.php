<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Attribute\SensitiveProperty;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Formatter\Object_\PublicVariableFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\PublicVariableFormatterTest\testFormatWorksWhenObjectHasMultipleSameNamePublicVariables; // phpcs:ignore

use function basename;
use function implode;
use function preg_quote;
use function sprintf;

#[CoversClass(PublicVariableFormatter::class)]
class PublicVariableFormatterTest extends TestCase
{
    public function testIsSkippedWhenObjectHasNoPublicVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();
        $object = new stdClass();

        $this->assertFalse($publicVariableFormatter->isHandling($object));
        $this->assertNull($publicVariableFormatter->format($caster, $object));
    }

    public function testFormatWorksWhenObjectHasOnePublicInjectedVariable(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();
        $object = new stdClass();
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

        $object = new stdClass();
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

    public function testFormatWorksWhenObjectHasOnePublicVariableWhichIsUninitialized(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();

        $object = new class
        {
            public string $foo;
        };

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $formatted = $publicVariableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s:\d+ \{',
                        '\$foo = \(uninitialized\)',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWhenObjectHasOnePublicVariableWhichIsSensitive(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter();

        $object = new class
        {
            #[SensitiveProperty]
            public string $foo = 'bar';
        };

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $formatted = $publicVariableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s:\d+ \{',
                        '\$foo = %s',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
                preg_quote(CasterInterface::SENSITIVE_MESSAGE_DEFAULT, '/'),
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

    public function testFormatWorksWhenWrapping(): void
    {
        $caster = Caster::create()->withIsWrapping(true);
        $publicVariableFormatter = new PublicVariableFormatter();

        $object = new class
        {
            public readonly object $foo;

            public function __construct()
            {
                $this->foo = new class
                {
                };
            }
        };

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $formatted = $publicVariableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        $this->assertSame(
            sprintf(
                implode("\n", [
                    '%s {',
                    '    $foo = %s',
                    '}',
                ]),
                Caster::makeNormalizedClassName(new ReflectionObject($object)),
                Caster::makeNormalizedClassName(new ReflectionObject($object->foo)),
            ),
            $formatted,
        );
    }
}
