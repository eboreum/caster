<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Annotation\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;
use Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatter;
use PHPUnit\Framework\TestCase;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties;
use TestResource\Unit\Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatterTest\testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities;

class DebugIdentifierAnnotationInterfaceFormatterTest extends TestCase
{
    public function testFormatReturnsNullWhenObjectIsNotQualified(): void
    {
        $caster = Caster::create();
        $debugIdentifierAnnotationInterfaceFormatter = new DebugIdentifierAnnotationInterfaceFormatter();
        $object = new \stdClass();

        $this->assertFalse($debugIdentifierAnnotationInterfaceFormatter->isHandling($object));
        $this->assertNull($debugIdentifierAnnotationInterfaceFormatter->format($caster, $object));
    }

    public function testFormatWorksWithAParentlessObject(): void
    {
        $caster = Caster::create();
        $debugIdentifierAnnotationInterfaceFormatter = new DebugIdentifierAnnotationInterfaceFormatter();

        $object = new class implements DebugIdentifierAnnotationInterface
        {
            public string $foo = '123';

            /** @DebugIdentifier */
            protected int $bar = 42;

            /** @DebugIdentifier */
            private float $baz = 3.14;
        };

        $this->assertTrue($debugIdentifierAnnotationInterfaceFormatter->isHandling($object));
        $this->assertMatchesRegularExpression(
            implode('', [
                '/',
                '^',
                'class@anonymous\/in\/.+\/DebugIdentifierAnnotationInterfaceFormatterTest\.php:\d+ \{',
                    '\$bar = \(int\) 42',
                    ', \$baz = \(float\) 3.14',
                '\}',
                '$',
                '/',
            ]),
            $debugIdentifierAnnotationInterfaceFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties(): void
    {
        $caster = Caster::create();
        $debugIdentifierAnnotationInterfaceFormatter = new DebugIdentifierAnnotationInterfaceFormatter();
        $className = testFormatWorksWithAOjectWithAParentButWithNoConflictingProperties\ClassA::class;

        $object = new $className();

        $propertyNameToReflectionProperties = $debugIdentifierAnnotationInterfaceFormatter
            ->getPropertyNameToReflectionProperties(new \ReflectionObject($object));
        $propertyNames = array_keys($propertyNameToReflectionProperties);

        $this->assertTrue($debugIdentifierAnnotationInterfaceFormatter->isHandling($object));
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
            $debugIdentifierAnnotationInterfaceFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities(): void
    {
        $caster = Caster::create();
        $debugIdentifierAnnotationInterfaceFormatter = new DebugIdentifierAnnotationInterfaceFormatter();
        $className = testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class;

        $object = new $className();

        $propertyNameToReflectionProperties = $debugIdentifierAnnotationInterfaceFormatter
            ->getPropertyNameToReflectionProperties(new \ReflectionObject($object));
        $propertyNames = array_keys($propertyNameToReflectionProperties);

        $this->assertTrue($debugIdentifierAnnotationInterfaceFormatter->isHandling($object));
        $this->assertCount(20, $propertyNameToReflectionProperties);

        $this->assertSame('publicPublicPublic', $propertyNames[0]);
        $this->assertCount(1, $propertyNameToReflectionProperties['publicPublicPublic']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['publicPublicPublic'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicPublic'][0]->isPublic());

        $this->assertSame('publicPublicProtected', $propertyNames[1]);
        $this->assertCount(1, $propertyNameToReflectionProperties['publicPublicProtected']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['publicPublicProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicProtected'][0]->isPublic());

        $this->assertSame('publicPublicPrivate', $propertyNames[2]);
        $this->assertCount(2, $propertyNameToReflectionProperties['publicPublicPrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['publicPublicPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicPrivate'][0]->isPublic());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['publicPublicPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPublicPrivate'][1]->isPrivate());

        $this->assertSame('publicProtectedProtected', $propertyNames[3]);
        $this->assertCount(1, $propertyNameToReflectionProperties['publicProtectedProtected']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['publicProtectedProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicProtectedProtected'][0]->isPublic());

        $this->assertSame('publicProtectedPrivate', $propertyNames[4]);
        $this->assertCount(2, $propertyNameToReflectionProperties['publicProtectedPrivate']);
        $this->assertTrue($propertyNameToReflectionProperties['publicProtectedPrivate'][0]->isPublic());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['publicProtectedPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['publicProtectedPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicProtectedPrivate'][1]->isPrivate());

        $this->assertSame('publicPrivatePrivate', $propertyNames[5]);
        $this->assertCount(3, $propertyNameToReflectionProperties['publicPrivatePrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['publicPrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPrivatePrivate'][0]->isPublic());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['publicPrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPrivatePrivate'][1]->isPrivate());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['publicPrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['publicPrivatePrivate'][2]->isPrivate());

        $this->assertSame('protectedProtectedProtected', $propertyNames[6]);
        $this->assertCount(1, $propertyNameToReflectionProperties['protectedProtectedProtected']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['protectedProtectedProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedProtectedProtected'][0]->isProtected());

        $this->assertSame('protectedProtectedPrivate', $propertyNames[7]);
        $this->assertCount(2, $propertyNameToReflectionProperties['protectedProtectedPrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['protectedProtectedPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedProtectedPrivate'][0]->isProtected());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['protectedProtectedPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedProtectedPrivate'][1]->isPrivate());

        $this->assertSame('protectedPrivatePrivate', $propertyNames[8]);
        $this->assertCount(3, $propertyNameToReflectionProperties['protectedPrivatePrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['protectedPrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedPrivatePrivate'][0]->isProtected());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['protectedPrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedPrivatePrivate'][1]->isPrivate());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['protectedPrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['protectedPrivatePrivate'][2]->isPrivate());

        $this->assertSame('privatePrivatePrivate', $propertyNames[9]);
        $this->assertCount(3, $propertyNameToReflectionProperties['privatePrivatePrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['privatePrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['privatePrivatePrivate'][0]->isPrivate());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['privatePrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['privatePrivatePrivate'][1]->isPrivate());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['privatePrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['privatePrivatePrivate'][2]->isPrivate());

        $this->assertSame('staticPrivatePrivatePrivate', $propertyNames[10]);
        $this->assertCount(3, $propertyNameToReflectionProperties['staticPrivatePrivatePrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][0]->isPrivate());
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][0]->isStatic());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][1]->isPrivate());
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][1]->isStatic());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][2]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][2]->isPrivate());
        $this->assertTrue($propertyNameToReflectionProperties['staticPrivatePrivatePrivate'][2]->isStatic());

        $this->assertSame('onlyInA', $propertyNames[11]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInA']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassA::class,
            $propertyNameToReflectionProperties['onlyInA'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInA'][0]->isPrivate());

        $this->assertSame('onlyInBAndCPublicPublic', $propertyNames[12]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInBAndCPublicPublic']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['onlyInBAndCPublicPublic'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicPublic'][0]->isPublic());

        $this->assertSame('onlyInBAndCPublicProtected', $propertyNames[13]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInBAndCPublicProtected']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['onlyInBAndCPublicProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicProtected'][0]->isPublic());

        $this->assertSame('onlyInBAndCPublicPrivate', $propertyNames[14]);
        $this->assertCount(2, $propertyNameToReflectionProperties['onlyInBAndCPublicPrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][0]->isPublic());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPublicPrivate'][1]->isPrivate());

        $this->assertSame('onlyInBAndCProtectedProtected', $propertyNames[15]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInBAndCProtectedProtected']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['onlyInBAndCProtectedProtected'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCProtectedProtected'][0]->isProtected());

        $this->assertSame('onlyInBAndCProtectedPrivate', $propertyNames[16]);
        $this->assertCount(2, $propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][0]->isProtected());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCProtectedPrivate'][1]->isPrivate());

        $this->assertSame('onlyInBAndCPrivatePrivate', $propertyNames[17]);
        $this->assertCount(2, $propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][0]->isProtected());
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][1]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInBAndCPrivatePrivate'][1]->isPrivate());

        $this->assertSame('onlyInB', $propertyNames[18]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInB']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class,
            $propertyNameToReflectionProperties['onlyInB'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInB'][0]->isPrivate());

        $this->assertSame('onlyInC', $propertyNames[19]);
        $this->assertCount(1, $propertyNameToReflectionProperties['onlyInC']);
        $this->assertSame(
            testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class,
            $propertyNameToReflectionProperties['onlyInC'][0]->getDeclaringClass()->getName(),
        );
        $this->assertTrue($propertyNameToReflectionProperties['onlyInC'][0]->isPrivate());

        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{',
                        '\$publicPublicPublic = \(string\(1\)\) "a"',
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
                        ', \$staticPrivatePrivatePrivate = \(string\(1\)\) "a"',
                        ', \\\\%s::\$staticPrivatePrivatePrivate = \(string\(1\)\) "b"',
                        ', \\\\%s::\$staticPrivatePrivatePrivate = \(string\(1\)\) "c"',
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
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassB::class, '/'),
                preg_quote(testFormatWorksWithSeveralLevelsOfClassesAndSeveralSameNamePropertiesWithVaryingVisibilities\ClassC::class, '/'),
            ),
            $debugIdentifierAnnotationInterfaceFormatter->format($caster, $object),
        );
    }

    public function testFormatWorksWhenNoPropertiesAreAnnotated(): void
    {
        $caster = Caster::create();
        $debugIdentifierAnnotationInterfaceFormatter = new DebugIdentifierAnnotationInterfaceFormatter();

        $object = new class implements DebugIdentifierAnnotationInterface
        {
            private string $foo = 'a';
        };

        $this->assertMatchesRegularExpression(
            implode('', [
                '/',
                '^',
                'class@anonymous\/in\/.+\/DebugIdentifierAnnotationInterfaceFormatterTest\.php:\d+ \{\}',
                '$',
                '/',
            ]),
            $debugIdentifierAnnotationInterfaceFormatter->format($caster, $object),
        );
    }
}
