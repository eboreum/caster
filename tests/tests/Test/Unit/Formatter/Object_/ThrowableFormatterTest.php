<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;
use Eboreum\Caster\Formatter\Object_\ThrowableFormatter;
use Exception;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function implode;
use function preg_quote;
use function sprintf;

#[CoversClass(ThrowableFormatter::class)]
class ThrowableFormatterTest extends TestCase
{
    public function testFormatWorksWithNonThrowables(): void
    {
        $caster = Caster::create();
        $throwableFormatter = new ThrowableFormatter();
        $object = new stdClass();

        $this->assertNull($throwableFormatter->format($caster, $object));
        $this->assertFalse($throwableFormatter->isHandling($object));
        $this->assertFalse($throwableFormatter->isIncludingTrace());
        $this->assertFalse($throwableFormatter->isIncludingTraceAsString());
    }

    public function testFormatWorksWithAnExceptionWithNoPrevious(): void
    {
        $caster = Caster::create();
        $throwableFormatter = new ThrowableFormatter();
        $object = new Exception('foo');

        $this->assertTrue($throwableFormatter->isHandling($object));
        $formatted = $throwableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
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
            $formatted,
        );
    }

    public function testFormatWorksWithAnExceptionWithMultiplePreviousFullyPrinted(): void
    {
        $caster = Caster::create();
        $caster = $caster->withDepthMaximum(new PositiveInteger(2));
        $throwableFormatter = new ThrowableFormatter();

        /** @var array<ObjectFormatterInterface> $formatters */
        $formatters = [$throwableFormatter];

        $caster = $caster->withCustomObjectFormatterCollection(new ObjectFormatterCollection($formatters));

        $third = new LogicException('baz', 2);
        $second = new RuntimeException('bar', 1, $third);
        $object = new Exception('foo', 0, $second);

        /**
         * We get 3 levels because, because we didn't not pass it through Caster->cast(...), and so the depth is off by
         * 1.
         */

        $this->assertTrue($throwableFormatter->isHandling($object));
        $formatted = $throwableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
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
            $formatted,
        );
    }

    public function testFormatWorksWithAnExceptionWithMultiplePreviousButLimitedToOne(): void
    {
        $caster = Caster::create();
        $caster = $caster->withDepthMaximum(new PositiveInteger(1));
        $throwableFormatter = new ThrowableFormatter();
        $throwableFormatter = $throwableFormatter->withDepthMaximum(new PositiveInteger(1));

        /** @var array<ObjectFormatterInterface> $formatters */
        $formatters = [$throwableFormatter];

        $caster = $caster->withCustomObjectFormatterCollection(new ObjectFormatterCollection($formatters));

        $third = new LogicException('baz', 2);
        $second = new RuntimeException('bar', 1, $third);
        $object = new Exception('foo', 0, $second);

        /**
         * We get 2 levels because, because we didn't not pass it through Caster->cast(...), and so the depth is off by
         * 1.
         */

        $this->assertTrue($throwableFormatter->isHandling($object));

        $formatted = $throwableFormatter->format($caster, $object);
        $this->assertIsString($formatted);
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
            $formatted,
        );
    }

    public function testFormatWorksWhenTraceAsStringIsIncluded(): void
    {
        $caster = Caster::create();
        $throwable = new Exception('foo');
        $throwableFormatter = (new ThrowableFormatter())->withIsIncludingTraceAsString(true);

        $formatted = $throwableFormatter->format($caster, $throwable);

        $this->assertIsString($formatted);

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\Exception \{',
                    '\$code = 0',
                    ', \$file = %s',
                    ', \$line = \d+',
                    ', \$message = %s',
                    ', \$previous = null',
                    ', traceAsString = "[^\n]+(\n[^\n]+)+"( \(sample\))?',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote($caster->cast(__FILE__), '/'),
                preg_quote($caster->cast('foo'), '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWhenTraceIsIncluded(): void
    {
        $caster = Caster::create();
        $throwable = new Exception('foo');
        $throwableFormatter = (new ThrowableFormatter())->withIsIncludingTrace(true);

        $formatted = $throwableFormatter->format($caster, $throwable);

        $this->assertIsString($formatted);

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\Exception \{',
                    '\$code = 0',
                    ', \$file = %s',
                    ', \$line = \d+',
                    ', \$message = %s',
                    ', \$previous = null',
                    ', trace = \[.+\]( \(sample\))?',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote($caster->cast(__FILE__), '/'),
                preg_quote($caster->cast('foo'), '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWhenWrapping(): void
    {
        $caster = Caster::create()->withIsWrapping(true)->withDepthCurrent(new PositiveInteger(2));
        $throwable = new Exception('foo');
        $throwableFormatter = (new ThrowableFormatter())
            ->withIsIncludingTrace(true)
            ->withIsIncludingTraceAsString(true);

        $formatted  = $throwableFormatter->format($caster, $throwable);

        $this->assertIsString($formatted);

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\Exception \{',
                    '\n    \$code = 0,',
                    '\n    \$file = %s,',
                    '\n    \$line = \d+,',
                    '\n    \$message = %s,',
                    '\n    \$previous = null,',
                    '\n    trace = \[',
                    '\n((        )[^\n]+\n)+',
                    '    \]( \(sample\))?,',
                    '\n    traceAsString = "[^\n]+(\n[^\n]+)+" \(indented\)( \(sample\))?',
                    '\n\}',
                    '$',
                    '/',
                ]),
                preg_quote($caster->cast(__FILE__), '/'),
                preg_quote($caster->cast('foo'), '/'),
            ),
            $formatted,
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

    public function testWithIsIncludingTraceAsStringWorks(): void
    {
        $throwableFormatterA = new ThrowableFormatter();
        $throwableFormatterB = $throwableFormatterA->withIsIncludingTraceAsString(true);

        $this->assertNotSame($throwableFormatterA, $throwableFormatterB);
        $this->assertFalse($throwableFormatterA->isIncludingTraceAsString());
        $this->assertTrue($throwableFormatterB->isIncludingTraceAsString());
    }

    public function testWithIsIncludingTraceWorks(): void
    {
        $throwableFormatterA = new ThrowableFormatter();
        $throwableFormatterB = $throwableFormatterA->withIsIncludingTrace(true);

        $this->assertNotSame($throwableFormatterA, $throwableFormatterB);
        $this->assertFalse($throwableFormatterA->isIncludingTrace());
        $this->assertTrue($throwableFormatterB->isIncludingTrace());
    }
}
