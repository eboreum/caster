<?php
declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Formatter\Object_\PublicVariableFormatter;
use PHPUnit\Framework\TestCase;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\PublicVariableFormatterTest\testFormatWorksWhenObjectHasMultipleSameNamePublicVariables;

class PublicVariableFormatterTest extends TestCase
{
    public function testIsSkippedWhenObjectHasNoPublicVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter;
        $object = new \stdClass;

        $this->assertFalse($publicVariableFormatter->isHandling($object));
        $this->assertNull($publicVariableFormatter->format($caster, $object));
    }

    public function testFormatWorksWhenObjectHasOnePublicInjectedVariable(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter;
        $object = new \stdClass;
        $object->foo = "bar";

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $this->assertSame(
            '\stdClass {$foo = "bar"}',
            $publicVariableFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWhenObjectHasMultiplePublicInjectedVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter;

        $object = new \stdClass;
        $object->foo = 1;
        $object->bar = null;
        $object->baz = "hmm";

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $this->assertSame(
            '\stdClass {$foo = 1, $bar = null, $baz = "hmm"}',
            $publicVariableFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWhenObjectHasOnePublicVariable(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter;

        $object = new class
        {
            public $foo = "bar";
        };

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            implode("", [
                '/',
                '^',
                'class@anonymous\/in\/.+\/PublicVariableFormatterTest\.php:\d+ \{',
                    '\$foo = "bar"',
                '\}',
                '$',
                '/',
            ]),
            $publicVariableFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWhenObjectHasMultiplePublicVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter;

        $object = new class
        {
            public $foo = 1;
            public $bar = null;
            public $baz = "hmm";
            private $private = null;
            protected $protected = null;
        };

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            implode("", [
                '/',
                '^',
                'class@anonymous\/in\/.+\/PublicVariableFormatterTest\.php:\d+ \{',
                    '\$foo = 1',
                    ', \$bar = null',
                    ', \$baz = "hmm"',
                '\}',
                '$',
                '/',
            ]),
            $publicVariableFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWhenObjectHasMultipleSameNamePublicVariables(): void
    {
        $caster = Caster::create();
        $publicVariableFormatter = new PublicVariableFormatter;

        $object = new testFormatWorksWhenObjectHasMultipleSameNamePublicVariables\ClassA;

        $this->assertTrue($publicVariableFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            sprintf(
                implode("", [
                    '/',
                    '^',
                    '\\\\%s \{',
                        '\$foo = "a"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(testFormatWorksWhenObjectHasMultipleSameNamePublicVariables\ClassA::class, "/"),
            ),
            $publicVariableFormatter->format($caster, $object),
        );
    }
}
