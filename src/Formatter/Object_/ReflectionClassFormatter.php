<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use ReflectionClass;

use function assert;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formats instances of \ReflectionClass.
 *
 * @see https://www.php.net/manual/en/class.reflectionclass.php
 */
class ReflectionClassFormatter extends AbstractObjectFormatter
{
    protected bool $isWrappingInClassName = true;

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof ReflectionClass); // Make phpstan happy

        $str = '';

        if ($caster->isPrependingType()) {
            if ($object->isEnum()) {
                $str = '(enum) ';
            } elseif ($object->isInterface()) {
                $str = '(interface) ';
            } elseif ($object->isTrait()) {
                $str = '(trait) ';
            } else {
                $str = '(class) ';
            }
        }

        $str .= Caster::makeNormalizedClassName($object);

        if ($this->isWrappingInClassName()) {
            $str = sprintf(
                '%s (%s)',
                Caster::makeNormalizedClassName(new ReflectionClass($object)),
                $str,
            );
        }

        return $str;
    }

    public function isHandling(object $object): bool
    {
        return ($object instanceof ReflectionClass);
    }

    public function isWrappingInClassName(): bool
    {
        return $this->isWrappingInClassName;
    }

    /**
     * Returns a clone.
     */
    public function withIsWrappingInClassName(bool $isWrappingInClassName): self
    {
        $clone = clone $this;
        $clone->isWrappingInClassName = $isWrappingInClassName;

        return $clone;
    }
}
