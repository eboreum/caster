<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\Formatter\WrappableInterface;
use ReflectionClass;
use ReflectionMethod;

use function array_walk;
use function assert;
use function implode;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formats instances of \ReflectionMethod.
 *
 * @see https://www.php.net/manual/en/class.reflectionmethod.php
 */
class ReflectionMethodFormatter extends AbstractObjectFormatter implements WrappableInterface
{
    protected bool $isRenderingParameters = true;

    protected bool $isRenderingReturnType = true;

    protected bool $isWrappingInClassName = true;

    protected ReflectionParameterFormatter $reflectionParameterFormatter;

    protected ReflectionTypeFormatter $reflectionTypeFormatter;

    public function __construct()
    {
        $this->reflectionParameterFormatter = (new ReflectionParameterFormatter())->withIsWrappingInClassName(false);
        $this->reflectionTypeFormatter = (new ReflectionTypeFormatter())->withIsWrappingInClassName(false);
    }

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof ReflectionMethod); // Make phpstan happy

        $str = sprintf(
            '%s%s%s',
            Caster::makeNormalizedClassName($object->getDeclaringClass()),
            ($object->isStatic() ? '::' : '->'),
            $object->getName(),
        );

        if ($this->isRenderingParameters()) {
            /** @var array<string> $parametersAsStrings */
            $parametersAsStrings = [];

            foreach ($object->getParameters() as $reflectionParameter) {
                $parametersAsStrings[] = $this
                    ->getReflectionParameterFormatter()
                    ->format($caster, $reflectionParameter);
            }

            if ($caster->isWrapping()) {
                array_walk($parametersAsStrings, static function (string &$parametersAsString) use ($caster): void {
                    $parametersAsString = $caster->getWrappingIndentationCharacters() . $parametersAsString;
                });

                $str .= sprintf(
                    "(\n%s\n)",
                    implode(",\n", $parametersAsStrings),
                );
            } else {
                $str .= '(' . implode(', ', $parametersAsStrings) . ')';
            }
        }

        if ($this->isRenderingReturnType() && $object->getReturnType()) {
            $str .= ': ' . $this->getReflectionTypeFormatter()->format($caster, $object->getReturnType());
        }

        if ($this->isWrappingInClassName()) {
            $str = sprintf(
                '%s (%s)',
                Caster::makeNormalizedClassName(new ReflectionClass($object)),
                $str,
            );
        }

        return $str;
    }

    public function getReflectionParameterFormatter(): ReflectionParameterFormatter
    {
        return $this->reflectionParameterFormatter;
    }

    public function getReflectionTypeFormatter(): ReflectionTypeFormatter
    {
        return $this->reflectionTypeFormatter;
    }

    public function isHandling(object $object): bool
    {
        return ($object instanceof ReflectionMethod);
    }

    public function isRenderingParameters(): bool
    {
        return $this->isRenderingParameters;
    }

    public function isRenderingReturnType(): bool
    {
        return $this->isRenderingReturnType;
    }

    public function isWrappingInClassName(): bool
    {
        return $this->isWrappingInClassName;
    }

    /**
     * Returns a clone.
     */
    public function withIsRenderingParameters(bool $isRenderingParameters): self
    {
        $clone = clone $this;
        $clone->isRenderingParameters = $isRenderingParameters;

        return $clone;
    }

    /**
     * Returns a clone.
     */
    public function withIsRenderingReturnType(bool $isRenderingReturnType): self
    {
        $clone = clone $this;
        $clone->isRenderingReturnType = $isRenderingReturnType;

        return $clone;
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

    /**
     * Returns a clone.
     */
    public function withReflectionParameterFormatter(ReflectionParameterFormatter $reflectionParameterFormatter): self
    {
        $clone = clone $this;
        $clone->reflectionParameterFormatter = $reflectionParameterFormatter;

        return $clone;
    }

    /**
     * Returns a clone.
     */
    public function withReflectionTypeFormatter(ReflectionTypeFormatter $reflectionTypeFormatter): self
    {
        $clone = clone $this;
        $clone->reflectionTypeFormatter = $reflectionTypeFormatter;

        return $clone;
    }
}
