<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\Collection\ElementInterface;
use Eboreum\Caster\Contract\Collection\ObjectCollectionInterface;
use Eboreum\Caster\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ObjectCollectionTest extends TestCase
{
    /**
     * @dataProvider genericDataProvider_getAllObjectCollectionClasses
     * @param \ReflectionClass<ObjectCollectionInterface<ElementInterface>> $reflectionClassCollection
     */
    public function testBasics(string $message, \ReflectionClass $reflectionClassCollection): void
    {
        $handledClassNameCollection = $reflectionClassCollection->getName();

        $handledClassName = $handledClassNameCollection::getHandledClassName();

        assert(is_string($handledClassName));
        assert(class_exists($handledClassName) || interface_exists($handledClassName));

        $reflectionClassHandledClass = new \ReflectionClass($handledClassName);

        $collectionA = new $handledClassNameCollection();

        $this->assertTrue($collectionA->isEmpty());

        $elements = [
            $this->mockHandledClass($reflectionClassHandledClass),
            $this->mockHandledClass($reflectionClassHandledClass),
            $this->mockHandledClass($reflectionClassHandledClass),
        ];

        assert($elements[0] instanceof ElementInterface);
        assert($elements[1] instanceof ElementInterface);
        assert($elements[2] instanceof ElementInterface);

        $collectionB = new $handledClassNameCollection(...$elements);

        $this->assertFalse($collectionB->isEmpty(), $message);
        $this->assertCount(3, $collectionB, $message);
        $this->assertSame($elements, $collectionB->toArray(), $message);
        $this->assertMatchesRegularExpression(
            sprintf(
                implode('', [
                    '/',
                    '^',
                    '\\\\%s \{',
                        '\$elements = \(array\(3\)\) \[',
                            '\(int\) 0 => \(object\) \\\\Mock_[a-zA-Z]+_[0-9a-f]{8}',
                            ', \(int\) 1 => \(object\) \\\\Mock_[a-zA-Z]+_[0-9a-f]{8}',
                            ', \(int\) 2 => \(object\) \\\\Mock_[a-zA-Z]+_[0-9a-f]{8}',
                        '\]',
                    '\}',
                    '$',
                    '/',
                ]),
                preg_quote($handledClassNameCollection, '/')
            ),
            $collectionB->toTextualIdentifier(Caster::getInstance()),
            $message,
        );

        $arrayIterator = $collectionB->getIterator();
        $this->assertSame($elements[0], $arrayIterator->current());

        $this->assertTrue($handledClassNameCollection::isElementAccepted($elements[0]), $message);
        $this->assertFalse($handledClassNameCollection::isElementAccepted(new \stdClass()), $message);
        $this->assertFalse($handledClassNameCollection::isElementAccepted(null), $message);
    }

    public function testConstructorThrowsExceptionWhenArgumentElementsContainsInvalidElements(): void
    {
        $elements = [
            new class extends \stdClass implements ElementInterface
            {
            },
            new class extends \DateTimeImmutable implements ElementInterface
            {
            },
            new class extends \stdClass implements ElementInterface
            {
            },
        ];

        try {
            new class (...$elements) extends AbstractObjectCollection
            {
                /**
                 * {@inheritDoc}
                 */
                public function getIterator(): \ArrayIterator
                {
                    return new \ArrayIterator($this->elements);
                }

                /**
                 * {@inheritDoc}
                 */
                public static function getHandledClassName(): string
                {
                    return 'stdClass';
                }
            };
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failed to construct class@anonymous\/in\/.+\/ObjectCollectionTest\.php:\d+ with arguments \{',
                            '\$elements = \.\.\.\(array\(3\)\) \[',
                                '\(int\) 0 => \(object\) class@anonymous\/in\/.+\/ObjectCollectionTest\.php:\d+',
                                ', \(int\) 1 => \(object\) class@anonymous\/in\/.+\/ObjectCollectionTest\.php:\d+',
                                ', \(int\) 2 => \(object\) class@anonymous\/in\/.+\/ObjectCollectionTest\.php:\d+',
                            '\]',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(Caster::class, '/'),
                    preg_quote(Caster::class, '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'In argument \$elements, 1\/3 values are invalid\.',
                        ' Must contain objects, instance of \\\\stdClass, exclusively, but it does not\.',
                        ' Invalid values include: \(array\(1\)\) \[',
                            '\(int\) 1 => \(object\) class@anonymous\/in\/.+\/ObjectCollectionTest\.php:\d+',
                        '\]',
                        '$',
                        '/',
                    ]),
                    preg_quote(Character::class, '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(null === $currentException);

            return;
        }

        $this->fail('Exception was never thrown.');
    }

    public function testToArrayWorks(): void
    {
        $elements = [
            new class extends \stdClass implements ElementInterface
            {
            },
        ];

        $collection = new class (...$elements) extends AbstractObjectCollection
        {
            /**
             * {@inheritDoc}
             */
            public function getIterator(): \ArrayIterator
            {
                return new \ArrayIterator($this->elements);
            }

            /**
             * {@inheritDoc}
             */
            public static function getHandledClassName(): string
            {
                return 'stdClass';
            }
        };

        $this->assertSame($elements, $collection->toArray());
    }

    /**
     * @throws \RuntimeException
     * @return array<int, array{0: string, 1: \ReflectionClass<ObjectCollectionInterface>}>
     */
    public function genericDataProvider_getAllObjectCollectionClasses(): array
    {
        $cases = [];

        $srcDirectory = dir(sprintf(
            '%s/src',
            dirname(TEST_ROOT_PATH),
        ));

        assert($srcDirectory instanceof \Directory);

        $pattern = sprintf(
            '%s/Collection/*.php',
            $srcDirectory->path,
        );

        foreach (\Eboreum\Caster\functions\rglob($pattern) as $filePath) {
            $className = 'Eboreum\\Caster\\' . str_replace(
                '/',
                '\\',
                preg_replace(
                    '/\.php$/',
                    '',
                    mb_substr(
                        $filePath,
                        mb_strlen($srcDirectory->path) + 1,
                    ),
                ),
            );

            if (false === class_exists($className)) {
                throw new \RuntimeException(sprintf(
                    'Class name %s does not exist, produced from file path %s',
                    Caster::getInternalInstance()->cast($className),
                    Caster::getInternalInstance()->cast($filePath),
                ));
            }

            if (is_subclass_of($className, ObjectCollectionInterface::class, true)) {
                $cases[] = [
                    'Class: \\' . $className,
                    new \ReflectionClass($className),
                ];
            }
        }

        $this->assertGreaterThan(0, count($cases));

        return $cases;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClassHandledClass
     */
    private function mockHandledClass(\ReflectionClass $reflectionClassHandledClass): MockObject
    {
        return $this
            ->getMockBuilder($reflectionClassHandledClass->getName())
            ->disableOriginalConstructor()
            ->getMock();
    }
}
