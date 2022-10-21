<?php

declare(strict_types=1);

namespace Eboreum\Caster\Abstraction\Collection;

use ArrayIterator;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Collection\ElementInterface;
use Eboreum\Caster\Contract\Collection\ObjectCollectionInterface;
use Eboreum\Caster\Exception\RuntimeException;
use ReflectionObject;
use Throwable;

use function count;
use function implode;
use function is_object;
use function sprintf;

/**
 * {@inheritDoc}
 *
 * An array collection which holds objects of a certain class, exclusively. The class is deterined by the method
 * `getHandledClassName`.
 *
 * @template T of ElementInterface
 * @implements ObjectCollectionInterface<T>
 */
abstract class AbstractObjectCollection implements ObjectCollectionInterface
{
    /** @var array<T> */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @param array<T> $elements
     *
     * @throws RuntimeException
     */
    public function __construct(array $elements = [])
    {
        try {
            $invalids = [];
            $className = static::getHandledClassName();

            foreach ($elements as $k => $element) {
                if (false === static::isElementAccepted($element)) {
                    $invalids[$k] = $element;
                }
            }

            if ($invalids) {
                throw new RuntimeException(sprintf(
                    implode('', [
                        'In argument $elements, %d/%d values are invalid. Must contain objects, instance of \\%s,',
                        ' exclusively, but it does not. Invalid values include: %s',
                    ]),
                    count($invalids),
                    count($elements),
                    $className,
                    Caster::create()->castTyped($invalids)
                ));
            }

            $this->elements = $elements;
        } catch (Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$elements = ...%s',
                Caster::create()->castTyped($elements),
            );

            throw new RuntimeException(sprintf(
                'Failed to construct %s with arguments {%s}',
                Caster::makeNormalizedClassName(new ReflectionObject($this)),
                implode(', ', $argumentsAsStrings),
            ), 0, $t);
        }
    }

    public static function isElementAccepted(mixed $element): bool
    {
        if (is_object($element)) {
            $className = static::getHandledClassName();

            return ($element instanceof $className);
        }

        return false;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<T>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    public function toTextualIdentifier(CasterInterface $caster): string
    {
        return sprintf(
            '%s {$elements = %s}',
            Caster::makeNormalizedClassName(new ReflectionObject($this)),
            Caster::getInternalInstance()->castTyped($this->toArray()),
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return ArrayIterator<(int|string), T>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function isEmpty(): bool
    {
        return !$this->elements;
    }
}
