<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\WrappableInterface;
use ReflectionObject;
use Throwable;

use function array_walk;
use function assert;
use function implode;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formats instances of \Throwable.
 */
class ThrowableFormatter extends AbstractObjectFormatter implements WrappableInterface
{
    protected PositiveInteger $depthMaximum;

    protected UnsignedInteger $messageMaximumLength;

    protected bool $isIncludingTrace = false;

    protected bool $isIncludingTraceAsString = false;

    public function __construct()
    {
        $this->depthMaximum = new PositiveInteger(100);
        $this->messageMaximumLength = new UnsignedInteger(5000);
    }

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof Throwable); // Make phpstan happy

        if (1 === $caster->getContext()->count()) {
            /*
             * Don't omit previous throwables after rather few of them (e.g. merely 3).
             */
            $caster = $caster->withDepthMaximum($this->getDepthMaximum());
        }

        $casterMessage = $caster
            ->withStringSampleSize(clone $this->getMessageMaximumLength());

        $elements = [
            '$code' => $caster->cast($object->getCode()),
            '$file' => $caster->cast($object->getFile()),
            '$line' => $caster->cast($object->getLine()),
            '$message' => $casterMessage->cast($object->getMessage()),
            '$previous' => $caster->cast($object->getPrevious()),
        ];

        if ($this->isIncludingTrace) {
            $elements['trace'] = $caster->cast($object->getTrace());
        }

        if ($this->isIncludingTraceAsString) {
            $elements['traceAsString'] = $caster->cast($object->getTraceAsString());
        }

        array_walk($elements, static function (string &$element, string $key): void {
            $element = $key . ' = ' . $element;
        });

        if ($caster->isWrapping()) {
            array_walk($elements, static function (string &$element) use ($caster): void {
                $element = $caster->getWrappingIndentationCharacters() . $element;
            });

            return sprintf(
                "%s {\n%s\n}",
                Caster::makeNormalizedClassName(new ReflectionObject($object)),
                implode(",\n", $elements),
            );
        }

        return sprintf(
            '%s {%s}',
            Caster::makeNormalizedClassName(new ReflectionObject($object)),
            implode(', ', $elements),
        );
    }

    public function withDepthMaximum(PositiveInteger $depthMaximum): ThrowableFormatter
    {
        $clone = clone $this;
        $clone->depthMaximum = $depthMaximum;

        return $clone;
    }

    public function withMessageMaximumLength(UnsignedInteger $messageMaximumLength): ThrowableFormatter
    {
        $clone = clone $this;
        $clone->messageMaximumLength = $messageMaximumLength;

        return $clone;
    }

    public function getDepthMaximum(): PositiveInteger
    {
        return $this->depthMaximum;
    }

    public function getMessageMaximumLength(): UnsignedInteger
    {
        return $this->messageMaximumLength;
    }

    public function isHandling(object $object): bool
    {
        return ($object instanceof Throwable);
    }

    public function isIncludingTrace(): bool
    {
        return $this->isIncludingTrace;
    }

    public function isIncludingTraceAsString(): bool
    {
        return $this->isIncludingTraceAsString;
    }

    /**
     * Returns a clone.
     */
    public function withIsIncludingTrace(bool $isIncludingTrace): static
    {
        $clone = clone $this;
        $clone->isIncludingTrace = $isIncludingTrace;

        return $clone;
    }

    /**
     * Returns a clone.
     */
    public function withIsIncludingTraceAsString(bool $isIncludingTraceAsString): static
    {
        $clone = clone $this;
        $clone->isIncludingTraceAsString = $isIncludingTraceAsString;

        return $clone;
    }
}
