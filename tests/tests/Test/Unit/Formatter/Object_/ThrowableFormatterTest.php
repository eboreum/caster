<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Formatter\Object_\ThrowableFormatter;
use PHPUnit\Framework\TestCase;

class ThrowableFormatterTest extends TestCase
{
    public function testFormatWorksWithNonThrowables(): void
    {
        $caster = Caster::create();
        $throwableFormatter = new ThrowableFormatter();
        $object = new \stdClass();

        $this->assertFalse($throwableFormatter->isHandling($object));
        $this->assertNull($throwableFormatter->format($caster, $object));
    }

    public function testFormatWorksWithAnExceptionWithNoPrevious(): void
    {
        $caster = Caster::create();
        $throwableFormatter = new ThrowableFormatter();
        $object = new \Exception('foo');

        $this->assertTrue($throwableFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            implode('', [
                '/',
                '^',
                '\\\\Exception \{',
                    '\$code = 0',
                    ', \$file = ".+"',
                    ', \$line = \d+',
                    ', \$message = "foo"',
                    ', \$previous = null',
                '\}',
                '$',
                '/',
            ]),
            $throwableFormatter->format($caster, $object)
        );
    }

    public function testFormatWorksWithAnExceptionWithMultiplePreviousFullyPrinted(): void
    {
        $caster = Caster::create();
        $caster = $caster->withDepthMaximum(new PositiveInteger(2));
        $throwableFormatter = new ThrowableFormatter();
        $caster = $caster->withCustomObjectFormatterCollection(
            new ObjectFormatterCollection(...[$throwableFormatter]),
        );
        $third = new \LogicException('baz', 2);
        $second = new \RuntimeException('bar', 1, $third);
        $object = new \Exception('foo', 0, $second);

        /**
         * We get 3 levels because, because we didn't not pass it through Caster->cast(...), and so the depth is off by
         * 1.
         */

        $this->assertTrue($throwableFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            implode('', [
                '/',
                '^',
                '\\\\Exception \{',
                    '\$code = 0',
                    ', \$file = ".+"',
                    ', \$line = \d+',
                    ', \$message = "foo"',
                    ', \$previous = \\\\RuntimeException \{',
                        '\$code = 1',
                        ', \$file = ".+"',
                        ', \$line = \d+',
                        ', \$message = "bar"',
                        ', \$previous = \\\\LogicException \{',
                            '\$code = 2',
                            ', \$file = ".+"',
                            ', \$line = \d+',
                            ', \$message = "baz"',
                            ', \$previous = null',
                        '\}',
                    '\}',
                '\}',
                '$',
                '/',
            ]),
            $throwableFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWithAnExceptionWithMultiplePreviousButLimitedToOne(): void
    {
        $caster = Caster::create();
        $caster = $caster->withDepthMaximum(new PositiveInteger(1));
        $throwableFormatter = new ThrowableFormatter();
        $throwableFormatter = $throwableFormatter->withDepthMaximum(new PositiveInteger(1));
        $caster = $caster->withCustomObjectFormatterCollection(
            new ObjectFormatterCollection(...[$throwableFormatter]),
        );

        $third = new \LogicException('baz', 2);
        $second = new \RuntimeException('bar', 1, $third);
        $object = new \Exception('foo', 0, $second);

        /**
         * We get 2 levels because, because we didn't not pass it through Caster->cast(...), and so the depth is off by
         * 1.
         */

        $this->assertTrue($throwableFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            implode('', [
                '/',
                '^',
                '\\\\Exception \{',
                    '\$code = 0',
                    ', \$file = ".+"',
                    ', \$line = \d+',
                    ', \$message = "foo"',
                    ', \$previous = \\\\RuntimeException \{',
                        '\$code = 1',
                        ', \$file = ".+"',
                        ', \$line = \d+',
                        ', \$message = "bar"',
                        ', \$previous = \\\\LogicException: \*\* OMITTED \*\* \(maximum depth of 1 reached\)',
                    '\}',
                '\}',
                '$',
                '/',
            ]),
            $throwableFormatter->format($caster, $object),
        );
    }

    public function testWithDepthMaximumWorks(): void
    {
        $throwableFormatterA = new ThrowableFormatter();
        $depthMaximumA = $throwableFormatterA->getDepthMaximum();

        $depthMaximumB = new PositiveInteger(42);
        $throwableFormatterB = $throwableFormatterA->withDepthMaximum($depthMaximumB);

        $this->assertNotSame($throwableFormatterA, $throwableFormatterB);
        $this->assertSame($depthMaximumA, $throwableFormatterA->getDepthMaximum());
        $this->assertSame($depthMaximumB, $throwableFormatterB->getDepthMaximum());
    }

    public function testWithMessageMaximumLengthWorks(): void
    {
        $throwableFormatterA = new ThrowableFormatter();
        $messageMaximumLengthA = $throwableFormatterA->getMessageMaximumLength();

        $messageMaximumLengthB = new UnsignedInteger(42);
        $throwableFormatterB = $throwableFormatterA->withMessageMaximumLength($messageMaximumLengthB);

        $this->assertNotSame($throwableFormatterA, $throwableFormatterB);
        $this->assertSame($messageMaximumLengthA, $throwableFormatterA->getMessageMaximumLength());
        $this->assertSame($messageMaximumLengthB, $throwableFormatterB->getMessageMaximumLength());
    }
}
