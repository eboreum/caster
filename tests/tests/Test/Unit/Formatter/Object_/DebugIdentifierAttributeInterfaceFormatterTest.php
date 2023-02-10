<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;
use Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatter;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties; // phpcs:ignore
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithATraitDirectlyOnTheFirstClass; // phpcs:ignore
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithATraitOnAParentClass; // phpcs:ignore
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities; // phpcs:ignore

use function array_keys;
use function assert;
use function basename;
use function implode;
use function is_string;
use function preg_quote;
use function sprintf;

class DebugIdentifierAttributeInterfaceFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $debugIdentifierAttributeInterfaceFormatter = new DebugIdentifierAttributeInterfaceFormatter();
        $object = new stdClass();

        $this->assertFalse($debugIdentifierAttributeInterfaceFormatter->isHandling($object));
        $this->assertNull($debugIdentifierAttributeInterfaceFormatter->format($caster, $object));
    }

    public function testFormatWorksWithAParentlessObject(): void
    {
        $caster = Caster::create();
        $debugIdentifierAttributeInterfaceFormatter = new DebugIdentifierAttributeInterfaceFormatter();

        $object = new class implements DebugIdentifierAttributeInterface
        {
            public string $foo = '123';

            #[DebugIdentifier]
            protected int $bar = 42;

            #[DebugIdentifier]
            private float $baz = 3.14; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
        };

        $formatted = $debugIdentifierAttributeInterfaceFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertTrue($debugIdentifierAttributeInterfaceFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s:\d+ \{',
                        '\$bar = \(int\) 42',
                        ', \$baz = \(float\) 3.14',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties(): void
    {
        $caster = Caster::create();
        $debugIdentifierAttributeInterfaceFormatter = new DebugIdentifierAttributeInterfaceFormatter();
        $className = testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties\ClassA::class;

        $object = new $className();

        $propertyNameToReflectionProperties = $debugIdentifierAttributeInterfaceFormatter
            ->getPropertyNameToReflectionProperties(new ReflectionObject($object));
        $propertyNames = array_keys($propertyNameToReflectionProperties);

        $this->assertTrue($debugIdentifierAttributeInterfaceFormatter->isHandling($object));
        $this->assertCount(2, $propertyNameToReflectionProperties);

        $this->assertSame('foo', $propertyNames[0]);
        $this->assertCount(1, $propertyNameToReflectionProperties['foo']);
        $this->assertSame(
            testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties\ClassA::class,
            $propertyNameToReflectionProperties['foo'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['foo'][0]->isPrivate());

        $this->assertSame('bar', $propertyNames[1]);
        $this->assertCount(1, $propertyNameToReflectionProperties['bar']);
        $this->assertSame(
            testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties\ClassB::class,
            $propertyNameToReflectionProperties['bar'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['bar'][0]->isPrivate());

        $formatted = $debugIdentifierAttributeInterfaceFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{',
                        '\$foo = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$bar = \(string\(1\)\) "b"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote($className, '/'),
                preg_quote(testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties\ClassB::class, '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities(): void
    {
        // @phpstan-ignore-next-line We only actually need this to resolve the namespace
        $prefix = testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities::class;
        $caster = Caster::create();
        $debugIdentifierAttributeInterfaceFormatter = new DebugIdentifierAttributeInterfaceFormatter();
        $className = $prefix . '\\ClassA';

        $object = new $className();

        $propertyNameToReflectionProperties = $debugIdentifierAttributeInterfaceFormatter
            ->getPropertyNameToReflectionProperties(new ReflectionObject($object));
        $propertyNames = array_keys($propertyNameToReflectionProperties);

        $this->assertTrue($debugIdentifierAttributeInterfaceFormatter->isHandling($object));
        $this->assertCount(20, $propertyNameToReflectionProperties);

        $this->assertSame('staticPrivatePrivatePrivate', $propertyNames[0]);
        $this->assertCount(3, $propertyNameToReflectionProperties['staticPrivatePrivatePrivate']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][0]->isPrivate());
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][0]->isStatic());
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][1]->isPrivate());
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][1]->isStatic());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][2]->isPrivate());
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][2]->isStatic());

        $this->assertSame('publicPublicPublic', $propertyNames[1]);
        $this->assertCount(1, $propertyNameToReflectionProperties['publicPublicPublic']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['publicPublicPublic'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicPublic'][0]->isPublic());

        $this->assertSame('publicPublicProtected', $propertyNames[2]);
        $this->assertCount(1, $propertyNameToReflectionProperties['publicPublicProtected']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['publicPublicProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicProtected'][0]->isPublic());

        $this->assertSame('publicPublicPrivate', $propertyNames[3]);
        $this->assertCount(2, $propertyNameToReflectionProperties['publicPublicPrivate']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['publicPublicPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicPrivate'][0]->isPublic());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['publicPublicPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicPrivate'][1]->isPrivate());

        $this->assertSame('publicProtectedProtected', $propertyNames[4]);
        $this->assertCount(1, $propertyNameToReflectionProperties['publicProtectedProtected']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['publicProtectedProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicProtectedProtected'][0]->isPublic());

        $this->assertSame('publicProtectedPrivate', $propertyNames[5]);
        $this->assertCount(2, $propertyNameToReflectionProperties['publicProtectedPrivate']);
        $this->assertTrue($propertyNameToReflectionProperties['publicProtectedPrivate'][0]->isPublic());
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['publicProtectedPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['publicProtectedPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicProtectedPrivate'][1]->isPrivate());

        $this->assertSame('publicPrivatePrivate', $propertyNames[6]);
        $this->assertCount(3, $propertyNameToReflectionProperties['publicPrivatePrivate']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['publicPrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPrivatePrivate'][0]->isPublic());
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['publicPrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPrivatePrivate'][1]->isPrivate());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['publicPrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPrivatePrivate'][2]->isPrivate());

        $this->assertSame('protectedProtectedProtected', $propertyNames[7]);
        $this->assertCount(1, $propertyNameToReflectionProperties['protectedProtectedProtected']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['protectedProtectedProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedProtectedProtected'][0]->isProtected());

        $this->assertSame('protectedProtectedPrivate', $propertyNames[8]);
        $this->assertCount(2, $propertyNameToReflectionProperties['protectedProtectedPrivate']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['protectedProtectedPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedProtectedPrivate'][0]->isProtected());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['protectedProtectedPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedProtectedPrivate'][1]->isPrivate());

        $this->assertSame('protectedPrivatePrivate', $propertyNames[9]);
        $this->assertCount(3, $propertyNameToReflectionProperties['protectedPrivatePrivate']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['protectedPrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedPrivatePrivate'][0]->isProtected());
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['protectedPrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedPrivatePrivate'][1]->isPrivate());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['protectedPrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedPrivatePrivate'][2]->isPrivate());

        $this->assertSame('privatePrivatePrivate', $propertyNames[10]);
        $this->assertCount(3, $propertyNameToReflectionProperties['privatePrivatePrivate']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['privatePrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['privatePrivatePrivate'][0]->isPrivate());
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['privatePrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['privatePrivatePrivate'][1]->isPrivate());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['privatePrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['privatePrivatePrivate'][2]->isPrivate());

        $this->assertSame('onlyInA', $propertyNames[11]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInA']);
        $this->assertSame(
            $prefix . '\\ClassA',
            $propertyNameToReflectionProperties['onlyInA'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInA'][0]->isPrivate());

        $this->assertSame('onlyInBAndCPublicPublic', $propertyNames[12]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInBAndCPublicPublic']);
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['onlyInBAndCPublicPublic'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicPublic'][0]->isPublic());

        $this->assertSame('onlyInBAndCPublicProtected', $propertyNames[13]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInBAndCPublicProtected']);
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['onlyInBAndCPublicProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicProtected'][0]->isPublic());

        $this->assertSame('onlyInBAndCPublicPrivate', $propertyNames[14]);
        $this->assertCount(2, $propertyNameToReflectionProperties['onlyInBAndCPublicPrivate']);
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][0]->isPublic());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][1]->isPrivate());

        $this->assertSame('onlyInBAndCProtectedProtected', $propertyNames[15]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInBAndCProtectedProtected']);
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['onlyInBAndCProtectedProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCProtectedProtected'][0]->isProtected());

        $this->assertSame('onlyInBAndCProtectedPrivate', $propertyNames[16]);
        $this->assertCount(2, $propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate']);
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][0]->isProtected());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][1]->isPrivate());

        $this->assertSame('onlyInBAndCPrivatePrivate', $propertyNames[17]);
        $this->assertCount(2, $propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate']);
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][0]->isProtected());
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][1]->isPrivate());

        $this->assertSame('onlyInB', $propertyNames[18]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInB']);
        $this->assertSame(
            $prefix . '\\ClassB',
            $propertyNameToReflectionProperties['onlyInB'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInB'][0]->isPrivate());

        $this->assertSame('onlyInC', $propertyNames[19]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInC']);
        $this->assertSame(
            $prefix . '\\ClassC',
            $propertyNameToReflectionProperties['onlyInC'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInC'][0]->isPrivate());

        $formatted = $debugIdentifierAttributeInterfaceFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{',
                        '\$staticPrivatePrivatePrivate = \(string\(1\)\) "a"',
                        ', \\\\%s::\$staticPrivatePrivatePrivate = \(string\(1\)\) "b"',
                        ', \\\\%s::\$staticPrivatePrivatePrivate = \(string\(1\)\) "c"',
                        ', \$publicPublicPublic = \(string\(1\)\) "a"',
                        ', \$publicPublicProtected = \(string\(1\)\) "a"',
                        ', \$publicPublicPrivate = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$publicPublicPrivate = \(string\(1\)\) "c"',
                        ', \$publicProtectedProtected = \(string\(1\)\) "a"',
                        ', \$publicProtectedPrivate = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$publicProtectedPrivate = \(string\(1\)\) "c"',
                        ', \$publicPrivatePrivate = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$publicPrivatePrivate = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$publicPrivatePrivate = \(string\(1\)\) "c"',
                        ', \$protectedProtectedProtected = \(string\(1\)\) "a"',
                        ', \$protectedProtectedPrivate = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$protectedProtectedPrivate = \(string\(1\)\) "c"',
                        ', \$protectedPrivatePrivate = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$protectedPrivatePrivate = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$protectedPrivatePrivate = \(string\(1\)\) "c"',
                        ', \$privatePrivatePrivate = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$privatePrivatePrivate = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$privatePrivatePrivate = \(string\(1\)\) "c"',
                        ', \$onlyInA = \(string\(1\)\) "a"',
                        ', \\\\%s-\>\$onlyInBAndCPublicPublic = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$onlyInBAndCPublicProtected = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$onlyInBAndCPublicPrivate = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$onlyInBAndCPublicPrivate = \(string\(1\)\) "c"',
                        ', \\\\%s-\>\$onlyInBAndCProtectedProtected = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$onlyInBAndCProtectedPrivate = \(string\(1\)\) "b"',
                        ', \\\\%s->\$onlyInBAndCProtectedPrivate = \(string\(1\)\) "c"',
                        ', \\\\%s-\>\$onlyInBAndCPrivatePrivate = \(string\(1\)\) "b"',
                        ', \\\\%s->\$onlyInBAndCPrivatePrivate = \(string\(1\)\) "c"',
                        ', \\\\%s-\>\$onlyInB = \(string\(1\)\) "b"',
                        ', \\\\%s-\>\$onlyInC = \(string\(1\)\) "c"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote($className, '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
                preg_quote($prefix . '\\ClassB', '/'),
                preg_quote($prefix . '\\ClassC', '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWithATraitDirectlyOnTheFirstClassWorks(): void
    {
        $object = new testFormatWorksWithATraitDirectlyOnTheFirstClass\ClassA();
        $caster = Caster::create();
        $debugIdentifierAttributeInterfaceFormatter = new DebugIdentifierAttributeInterfaceFormatter();

        $formatted = $debugIdentifierAttributeInterfaceFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{',
                    '\$foo = \(string\(1\)\) "a"',
                    ', \$baz = \(string\(1\)\) "c"',
                    ', \$bar = \(string\(1\)\) "b"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(testFormatWorksWithATraitDirectlyOnTheFirstClass\ClassA::class, '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWithATraitOnAParentClassWorks(): void
    {
        $object = new testFormatWorksWithATraitOnAParentClass\ClassA();
        $caster = Caster::create();
        $debugIdentifierAttributeInterfaceFormatter = new DebugIdentifierAttributeInterfaceFormatter();

        $formatted = $debugIdentifierAttributeInterfaceFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{',
                    '\$foo = \(string\(1\)\) "a"',
                    ', \\\\%s-\>\$bar = \(string\(1\)\) "b"',
                    ', \\\\%s-\>\$baz = \(string\(1\)\) "c"',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote(testFormatWorksWithATraitOnAParentClass\ClassA::class, '/'),
                preg_quote(testFormatWorksWithATraitOnAParentClass\ClassB::class, '/'),
                preg_quote(testFormatWorksWithATraitOnAParentClass\ClassB::class, '/'),
            ),
            $formatted,
        );
    }

    public function testFormatWorksWhenNoPropertiesAreAnnotated(): void
    {
        $caster = Caster::create();
        $debugIdentifierAttributeInterfaceFormatter = new DebugIdentifierAttributeInterfaceFormatter();

        $object = new class implements DebugIdentifierAttributeInterface
        {
            private string $foo = 'a'; // @phpstan-ignore-line Suppression code babdc1d2; see README.md
        };

        $formatted = $debugIdentifierAttributeInterfaceFormatter->format($caster, $object);
        $this->assertIsString($formatted);
        assert(is_string($formatted)); // Make phpstan happy

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    'class@anonymous\/in\/.+\/%s:\d+ \{\}',
                    '$',
                    '/',
                ]),
                preg_quote(basename(__FILE__), '/'),
            ),
            $formatted,
        );
    }
}
