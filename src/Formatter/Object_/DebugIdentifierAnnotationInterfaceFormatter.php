<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Doctrine\Common\Annotations\AnnotationReader;
use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Annotation\DebugIdentifier;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\DebugIdentifierAnnotationInterface;

/**
 * Handles classes, which implement `DebugIdentifierAnnotationInterface`.
 *
 * Requires package: doctrine/annotations
 */
class DebugIdentifierAnnotationInterfaceFormatter extends AbstractObjectFormatter
{
    public static function doReflectionPropertiesHaveSameVisibilityWhenInsideA(
        \ReflectionProperty $a,
        \ReflectionProperty $b
    ): bool {
        return (
            $a->isPublic() && $b->isPublic()
            || $a->isPublic() && $b->isProtected()
            || $a->isProtected() && $b->isProtected()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        $caster = $caster->withIsPrependingType(true);
        $reflectionObject = new \ReflectionObject($object);

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
     * @return array<string, array<int, \ReflectionProperty>>
     */
    public function getPropertyNameToReflectionProperties(\ReflectionObject $reflectionObject): array
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

        $annotationReader = new AnnotationReader();
        $reflectionClassCurrent = $reflectionObject;
        $propertyNameToReflectionProperties = [];

        while ($reflectionClassCurrent) {
            foreach ($reflectionClassCurrent->getProperties() as $reflectionProperty) {
                $annotation = $annotationReader->getPropertyAnnotation($reflectionProperty, DebugIdentifier::class);

                if ($annotation) {
                    if (array_key_exists($reflectionProperty->getName(), $propertyNameToReflectionProperties)) {
                        $indexPrevious = count($propertyNameToReflectionProperties[$reflectionProperty->getName()]) - 1;

                        if ($indexPrevious >= 0) {
                            $reflectionPropertyPrevious = (
                                $propertyNameToReflectionProperties[$reflectionProperty->getName()][$indexPrevious]
                                ?? null
                            );

                            if ($reflectionPropertyPrevious) {
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

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return (
            class_exists(AnnotationReader::class)
            && $object instanceof DebugIdentifierAnnotationInterface
        );
    }
}
