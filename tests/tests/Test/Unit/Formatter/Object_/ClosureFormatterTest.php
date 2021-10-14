<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Formatter\Object_\ClosureFormatter;
use PHPUnit\Framework\TestCase;

class ClosureFormatterTest extends TestCase
{
    const A_CONSTANT = "foo";

    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $closureFormatter = new ClosureFormatter;
        $object = new \stdClass;

        $this->assertFalse($closureFormatter->isHandling($object));
        $this->assertNull($closureFormatter->format($caster, $object));
    }

    /**
     * @dataProvider dataProvider_testFormatWorks
     */
    public function testFormatWorks(
        string $message,
        \Closure $closure,
        string $expectedArguments
    ): void
    {
        $caster = Caster::create();
        $closureFormatter = new ClosureFormatter;

        $this->assertTrue($closureFormatter->isHandling($closure), $message);
        $this->assertMatchesRegularExpression(
            sprintf(
                implode("", [
                    '/',
                    '^',
                    '\\\\Closure\(%s\)',
                    '$',
                    '/',
                ]),
                preg_quote($expectedArguments, "/"),
            ),
            $closureFormatter->format($caster, $closure),
            $message,
        );
    }

    /**
     * @return array<int, array{string, \Closure, string}>
     */
    public function dataProvider_testFormatWorks(): array
    {
        return [
            [
                "\Closure with no arguments.",
                function(){},
                "",
            ],
            [
                "\Closure with 1 argument. No default value.",
                function(int $a){},
                "int \$a",
            ],
            [
                "\Closure with 1 argument. With default value.",
                function(int $a = 42){},
                "int \$a = 42",
            ],
            [
                "\Closure with 1 argument. With default value being a global constant.",
                function(int $a = \PHP_INT_MAX){},
                "int \$a = PHP_INT_MAX",
            ],
            [
                "\Closure with 1 argument. With default value being a constant with a `self` reference.",
                function(int $a = self::A_CONSTANT){},
                "int \$a = self::A_CONSTANT",
            ],
            [
                "\Closure with 1 argument. With default value being a constant with a class name reference.",
                function(int $a = ClosureFormatterTest::A_CONSTANT){},
                sprintf(
                    "int \$a = \\%s::A_CONSTANT",
                    ClosureFormatterTest::class,
                ),
            ],
            [
                "\Closure with 3 arguments. No default values.",
                function(int $a, string $b, bool $c){},
                "int \$a, string \$b, bool \$c",
            ],
            [
                "\Closure with 3 arguments. With 3 default values.",
                function(int $a = 42, string $b = "foo", bool $c = true){},
                "int \$a = 42, string \$b = \"foo\", bool \$c = true",
            ],
            [
                "\Closure with 1 typed variadic argument.",
                function(int ...$a){},
                "int ...\$a",
            ],
            [
                "\Closure with 1 typed variadic argument being nullable.",
                function(?int ...$a){},
                "?int ...\$a",
            ],
            [
                "\Closure with 1 typed argument passed by reference.",
                function(int &$a){},
                "int &\$a",
            ],
            [
                "\Closure with 1 typed argument passed by reference being nullable.",
                function(?int &$a){},
                "?int &\$a",
            ],
            [
                "The big one.",
                function($a, &$b, int $c, bool $d, \stdClass $e, array $f = ["lala"], ?string ...$z){},
                "\$a, &\$b, int \$c, bool \$d, \\stdClass \$e, array \$f = [0 => \"lala\"], ?string ...\$z",
            ],
        ];
    }
}
