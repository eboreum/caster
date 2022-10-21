<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Attribute\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\DebugIdentifierAttributeInterface;
use ReflectionObject;
use ReflectionProperty;

use function array_key_exists;
use function count;
use function implode;
use function sprintf;

/**
 * @inheritDoc
 *
 * Handles classes, which implement `DebugIdentifierAttributeInterface`.
 */
class DebugIdentifierAttributeInterfaceFormatter extends AbstractObjectFormatter
{
    public static function doReflectionPropertiesHaveSameVisibilityWhenInsideA(
        ReflectionProperty $a,
        ReflectionProperty $b,
    ): bool {
        return (
            $a->isPublic() && $b->isPublic()
            || $a->isPublic() && $b->isProtected()
            || $a->isProtected() && $b->isProtected()
        );
    }

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        $caster = $caster->withIsPrependingType(true);
        $reflectionObject = new ReflectionObject($object);

        $map = $this->getPropertyNameToReflectionProperties($reflectionObject);
        $segments = [];

        foreach ($map as $propertyName => $reflectionProperties) {
            foreach ($reflectionProperties as $reflectionProperty) {
                $reflectionProperty->setAccessible(true);

                $segment = sprintf(
                    '$%s = %s',
                    $propertyName,
                    (
                        $reflectionProperty->isInitialized($object)
                        ? $caster->cast($reflectionProperty->getValue($object))
                        : '(uninitialized)'
                    ),
                );

                $hasClassPrefix = (
                    $reflectionProperty->getDeclaringClass()->getName() !== $reflectionObject->getName()
                );

                if ($hasClassPrefix) {
                    $segment = sprintf(
                        '%s%s%s',
                        Caster::makeNormalizedClassName($reflectionProperty->getDeclaringClass()),
                        (
                            $reflectionProperty->isStatic()
                            ? '::'
                            : '->'
                        ),
                        $segment,
                    );
                }

                $segments[] = $segment;
            }
        }

        $return = Caster::makeNormalizedClassName($reflectionObject);

        if ($segments) {
            $return .= sprintf(
                ' {%s}',
                implode(', ', $segments),
            );
        } else {
            $return .= ' {}';
        }

        return $return;
    }

    /**
     * @return array<string, array<int, ReflectionProperty>>
     */
    public function getPropertyNameToReflectionProperties(ReflectionObject $reflectionObject): array
    {
        /*
         * Visibility hierachy goes: private > protected > public. Left to right, with the left side having more
         * influence/significance.
         *
         * Class A extends B {}
         *
         * Impossible (right-to-left, which is not allowed):
         *     A            B
         *     protected    public
         *     private      public
         *
         * A and B have different values (different visibility when inside A):
         *     A            B
         *     public       private
         *     protected    private
         *     private      private
         *
         * A overrides B (same visibility level when inside A):
         *     A            B
         *     public       public
         *     public       protected
         *     protected    protected
         */

        $reflectionClassCurrent = $reflectionObject;
        $propertyNameToReflectionProperties = [];

        while ($reflectionClassCurrent) {
            foreach ($reflectionClassCurrent->getProperties() as $reflectionProperty) {
                $debugIdentifiers = $reflectionProperty->getAttributes(DebugIdentifier::class);

                if ($debugIdentifiers) {
                    $name = $reflectionProperty->getName();

                    if (array_key_exists($name, $propertyNameToReflectionProperties)) {
                        $indexPrevious = count($propertyNameToReflectionProperties[$name]) - 1;

                        if ($indexPrevious >= 0) {
                            $exists = array_key_exists(
                                $indexPrevious,
                                $propertyNameToReflectionProperties[$name],
                            );

                            if ($exists) {
                                $reflectionProperties = $propertyNameToReflectionProperties[$name];
                                $reflectionPropertyPrevious = $reflectionProperties[$indexPrevious];

                                if (
                                    static::doReflectionPropertiesHaveSameVisibilityWhenInsideA(
                                        $reflectionPropertyPrevious,
                                        $reflectionProperty,
                                    )
                                ) {
                                    /*
                                     * Ignore same-name properties with the same (inside class) visibility. These will
                                     * only be displayed once and without a class name prefix, unless other conflicts
                                     * are encountered.
                                     */

                                    continue;
                                }
                            }
                        }
                    }

                    if (
                        false === array_key_exists($reflectionProperty->getName(), $propertyNameToReflectionProperties)
                    ) {
                        $propertyNameToReflectionProperties[$reflectionProperty->getName()] = [];
                    }

                    $propertyNameToReflectionProperties[$reflectionProperty->getName()][] = $reflectionProperty;
                }
            }

            $reflectionClassCurrent = $reflectionClassCurrent->getParentClass();
        }

        return $propertyNameToReflectionProperties;
    }

    public function isHandling(object $object): bool
    {
        return $object instanceof DebugIdentifierAttributeInterface;
    }
}
