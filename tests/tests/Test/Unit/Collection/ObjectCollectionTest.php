<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster\Collection;

use ArrayIterator;
use DateTimeImmutable;
use Directory;
use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\Collection\ElementInterface;
use Eboreum\Caster\Contract\Collection\ObjectCollectionInterface;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

use function assert;
use function basename;
use function class_exists;
use function count;
use function dir;
use function dirname;
use function Eboreum\Caster\functions\rglob;
use function implode;
use function interface_exists;
use function is_string;
use function is_subclass_of;
use function mb_strlen;
use function mb_substr;
use function preg_quote;
use function preg_replace;
use function sprintf;
use function str_replace;

class ObjectCollectionTest extends TestCase
{
    /**
     * @param ReflectionClass<ObjectCollectionInterface<ElementInterface>> $reflectionClassCollection
     *
     * @dataProvider genericDataProviderGetAllObjectCollectionClasses
     */
    public function testBasics(string $message, ReflectionClass $reflectionClassCollection): void
    {
        $handledClassNameCollection = $reflectionClassCollection->getName();

        $handledClassName = $handledClassNameCollection::getHandledClassName();

        assert(is_string($handledClassName)); // Make phpstan happy
        assert(class_exists($handledClassName) || interface_exists($handledClassName)); // Make phpstan happy

        $reflectionClassHandledClass = new ReflectionClass($handledClassName);

        $collectionA = new $handledClassNameCollection();

        $this->assertTrue($collectionA->isEmpty());

        $elements = [
            $this->mockHandledClass($reflectionClassHandledClass),
            $this->mockHandledClass($reflectionClassHandledClass),
            $this->mockHandledClass($reflectionClassHandledClass),
        ];

        assert($elements[0] instanceof ElementInterface); // Make phpstan happy
        assert($elements[1] instanceof ElementInterface); // Make phpstan happy
        assert($elements[2] instanceof ElementInterface); // Make phpstan happy

        $collectionB = new $handledClassNameCollection($elements);

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
        $this->assertFalse($handledClassNameCollection::isElementAccepted(new stdClass()), $message);
        $this->assertFalse($handledClassNameCollection::isElementAccepted(null), $message);
    }

    public function testConstructorThrowsExceptionWhenArgumentElementsContainsInvalidElements(): void
    {
        $elements = [
            new class extends stdClass implements ElementInterface
            {
            },
            new class extends DateTimeImmutable implements ElementInterface
            {
            },
            new class extends stdClass implements ElementInterface
            {
            },
        ];

        try {
            new class ($elements) extends AbstractObjectCollection
            {
                public static function getHandledClassName(): string
                {
                    return 'stdClass';
                }

                public function getIterator(): ArrayIterator
                {
                    return new ArrayIterator($this->elements);
                }
            };
        } catch (Exception $e) { // @phpstan-ignore-line Suppression code 136348fe; see README.md
            $currentException = $e;
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'Failed to construct \\\\%s@anonymous\/in\/.+\/%s:\d+ with arguments \{',
                            '\$elements = \.\.\.\(array\(3\)\) \[',
                                '\(int\) 0 => \(object\) \\\\stdClass@anonymous\/in\/.+\/%s:\d+',
                                ', \(int\) 1 => \(object\) \\\\DateTimeImmutable@anonymous\/in\/.+\/%s:\d+',
                                ', \(int\) 2 => \(object\) \\\\stdClass@anonymous\/in\/.+\/%s:\d+',
                            '\]',
                        '\}',
                        '$',
                        '/',
                    ]),
                    preg_quote(AbstractObjectCollection::class, '/'),
                    preg_quote(basename(__FILE__), '/'),
                    preg_quote(basename(__FILE__), '/'),
                    preg_quote(basename(__FILE__), '/'),
                    preg_quote(basename(__FILE__), '/'),
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(RuntimeException::class, $currentException::class);
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode('', [
                        '/',
                        '^',
                        'In argument \$elements, 1\/3 values are invalid\.',
                        ' Must contain objects, instance of \\\\stdClass, exclusively, but it does not\.',
                        ' Invalid values include: \(array\(1\)\) \[',
                            '\(int\) 1 => \(object\) \\\\DateTimeImmutable@anonymous\/in\/.+\/%s:\d+', // phpcs:ignore
                        '\]',
                        '$',
                        '/',
                    ]),
                    preg_quote(basename(__FILE__), '/'),
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
            new class extends stdClass implements ElementInterface
            {
            },
        ];

        $collection = new class ($elements) extends AbstractObjectCollection
        {
            public static function getHandledClassName(): string
            {
                return 'stdClass';
            }

            public function getIterator(): ArrayIterator
            {
                return new ArrayIterator($this->elements);
            }
        };

        $this->assertSame($elements, $collection->toArray());
    }

    /**
     * @return array<array{string, ReflectionClass<ObjectCollectionInterface<ElementInterface>>}>
     *
     * @throws \RuntimeException
     */
    public function genericDataProviderGetAllObjectCollectionClasses(): array
    {
        $cases = [];

        $srcDirectory = dir(sprintf(
            '%s/src',
            dirname(TEST_ROOT_PATH),
        ));

        assert($srcDirectory instanceof Directory); // Make phpstan happy

        $pattern = sprintf(
            '%s/Collection/*.php',
            $srcDirectory->path,
        );

        foreach (rglob($pattern) as $filePath) {
            $className = mb_substr(
                $filePath,
                mb_strlen($srcDirectory->path) + 1,
            );

            assert(is_string($className)); // Make phpstan happy

            $className = preg_replace(
                '/\.php$/',
                '',
                $className,
            );

            assert(is_string($className)); // Make phpstan happy

            $className = 'Eboreum\\Caster\\' . str_replace(
                '/',
                '\\',
                $className,
            );

            assert(is_string($className)); // Make phpstan happy

            if (false === class_exists($className)) {
                throw new \RuntimeException(sprintf(
                    'Class name %s does not exist, produced from file path %s',
                    Caster::getInternalInstance()->cast($className),
                    Caster::getInternalInstance()->cast($filePath),
                ));
            }

            if (is_subclass_of($className, ObjectCollectionInterface::class, true)) {
                /** @var ReflectionClass<ObjectCollectionInterface<ElementInterface>> $reflectionClass */
                $reflectionClass = new ReflectionClass($className);

                $cases[] = [
                    'Class: \\' . $className,
                    $reflectionClass,
                ];
            }
        }

        $this->assertGreaterThan(0, count($cases));

        return $cases;
    }

    /**
     * @param ReflectionClass<object> $reflectionClassHandledClass
     */
    private function mockHandledClass(ReflectionClass $reflectionClassHandledClass): MockObject
    {
        return $this
            ->getMockBuilder($reflectionClassHandledClass->getName())
            ->disableOriginalConstructor()
            ->getMock();
    }
}
