<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Caster;

/**
 * Prints class properties with public access.
 *
 * As this class utilizes the Reflection API (https://www.php.net/manual/en/book.reflection.php), which is slow,
 * this class should mainly be used in failure scenarios, e.g. as part of building an exception message.
 */
class PublicVariableFormatter extends AbstractObjectFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        $propertySequenceAsString = $this->getPropertySequenceAsString($caster, $object);

        return sprintf(
            "%s {%s}",
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            $propertySequenceAsString,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return boolval($this->getPropertyNameToReflectionProperty(new \ReflectionObject($object)));
    }

    /**
     * @return array<string, \ReflectionProperty>
     */
    protected function getPropertyNameToReflectionProperty(\ReflectionObject $reflectionObject): array
    {
        $reflectionClassCurrent = $reflectionObject;
        $propertyNameToReflectionProperty = [];

        while ($reflectionClassCurrent) {
            foreach ($reflectionClassCurrent->getProperties() as $reflectionProperty) {
                if ($reflectionProperty->isPublic()) {
                    if (array_key_exists($reflectionProperty->getName(), $propertyNameToReflectionProperty)) {
                        continue;
                    }

                    $propertyNameToReflectionProperty[$reflectionProperty->getName()] = $reflectionProperty;
                }
            }

            $reflectionClassCurrent = $reflectionClassCurrent->getParentClass();
        }

        return $propertyNameToReflectionProperty;
    }

    protected function getPropertySequenceAsString(Caster $caster, object $object): string
    {
        $reflectionObject = new \ReflectionObject($object);
        $propertyNameToReflectionProperty = $this->getPropertyNameToReflectionProperty($reflectionObject);
        $segments = [];

        foreach ($propertyNameToReflectionProperty as $propertyName => $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            $segments[] = sprintf(
                "\$%s = %s",
                $propertyName,
                (
                    $reflectionProperty->isInitialized($object)
                    ? $caster->cast($reflectionProperty->getValue($object))
                    : "(uninitialized)"
                ),
            );
        }

        return implode(", ", $segments);
    }
}
