<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Attribute\SensitiveProperty;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\WrappableInterface;
use ReflectionObject;
use ReflectionProperty;

use function array_key_exists;
use function array_walk;
use function boolval;
use function implode;
use function sprintf;

/**
 * @inheritDoc
 *
 * Prints class properties with public access.
 *
 * As this class utilizes the Reflection API (https://www.php.net/manual/en/book.reflection.php), which is slow,
 * this class should mainly be used in failure scenarios, e.g. as part of building an exception message.
 */
class PublicVariableFormatter extends AbstractObjectFormatter implements WrappableInterface
{
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        $propertySequenceAsString = $this->getPropertySequenceAsString($caster, $object);

        return sprintf(
            '%s {%s%s%s}',
            Caster::makeNormalizedClassName(new ReflectionObject($object)),
            ($caster->isWrapping() ? "\n" : ''),
            $propertySequenceAsString,
            ($caster->isWrapping() ? "\n" : ''),
        );
    }

    public function isHandling(object $object): bool
    {
        return boolval($this->getPropertyNameToReflectionProperty(new ReflectionObject($object)));
    }

    /**
     * @return array<string, ReflectionProperty>
     */
    protected function getPropertyNameToReflectionProperty(ReflectionObject $reflectionObject): array
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

    protected function getPropertySequenceAsString(CasterInterface $caster, object $object): string
    {
        $reflectionObject = new ReflectionObject($object);
        $propertyNameToReflectionProperty = $this->getPropertyNameToReflectionProperty($reflectionObject);
        $segments = [];

        foreach ($propertyNameToReflectionProperty as $propertyName => $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            /** @var bool $isSensitive */
            $isSensitive = (bool) ($reflectionProperty->getAttributes(SensitiveProperty::class)[0] ?? false);

            $segment = sprintf(
                '$%s = ',
                $propertyName,
            );

            if ($reflectionProperty->isInitialized($object)) {
                if ($isSensitive) {
                    $segment .= $caster->getSensitiveMessage();
                } else {
                    $segment .= $caster->cast($reflectionProperty->getValue($object));
                }
            } else {
                $segment .= '(uninitialized)';
            }

            $segments[] = $segment;
        }

        if ($caster->isWrapping()) {
            array_walk($segments, static function (string &$segment) use ($caster): void {
                $segment = $caster->getWrappingIndentationCharacters() . $segment;
            });

            return implode("\n,", $segments);
        }

        return implode(', ', $segments);
    }
}
