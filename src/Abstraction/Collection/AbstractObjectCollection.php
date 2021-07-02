<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Abstraction\Collection;

use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Collection\ElementInterface;
use Eboreum\Caster\Contract\Collection\ObjectCollectionInterface;
use Eboreum\Caster\Exception\RuntimeException;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;

/**
 * {@inheritDoc}
 *
 * An array collection which holds objects of a certain class, exclusively. The class is deterined by the method
 * `getHandledClassName`.
 */
abstract class AbstractObjectCollection implements ObjectCollectionInterface
{
    /**
     * @var array<int, ElementInterface>
     */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function __construct(ElementInterface ...$elements)
    {
        try {
            if ($elements) {
                $invalids = [];
                $className = static::getHandledClassName();

                foreach ($elements as $k => $element) {
                    if (false === static::isElementAccepted($element)) {
                        $invalids[$k] = $element;
                    }
                }

                if ($invalids) {
                    throw new RuntimeException(sprintf(
                        implode("", [
                            "In argument \$elements, %d/%d values are invalid. Must contain objects, instance of \\%s,",
                            " exclusively, but it does not. Invalid values include: %s",
                        ]),
                        count($invalids),
                        count($elements),
                        $className,
                        Caster::create()->castTyped($invalids)
                    ));
                }
            }

            $this->elements = $elements;
        } catch (\Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                "\$elements = ...%s",
                Caster::create()->castTyped($elements),
            );

            throw new RuntimeException(sprintf(
                "Failed to construct %s with arguments {%s}",
                Caster::makeNormalizedClassName(new \ReflectionObject($this)),
                implode(", ", $argumentsAsStrings),
            ), 0, $t);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     */
    public function toTextualIdentifier(CasterInterface $caster): string
    {
        return sprintf(
            "%s {\$elements = %s}",
            Caster::makeNormalizedClassName(new \ReflectionObject($this)),
            Caster::getInternalInstance()->castTyped($this->toArray()),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public static function isElementAccepted($element): bool
    {
        if (is_object($element)) {
            $className = static::getHandledClassName();

            return ($element instanceof $className);
        }

        return false;
    }
}
