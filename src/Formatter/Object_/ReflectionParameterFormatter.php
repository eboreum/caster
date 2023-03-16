<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Exception\RuntimeException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

use function array_key_exists;
use function assert;
use function escapeshellarg;
use function implode;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * @inheritDoc
 *
 * Formats instances of \ReflectionParameter.
 *
 * @see https://www.php.net/manual/en/class.reflectionparameter.php
 */
class ReflectionParameterFormatter extends AbstractObjectFormatter
{
    public static function getDefaultValueConstantRegex(): string
    {
        return sprintf(
            implode('', [
                '/',
                '^',
                '(',
                    '(',
                        '?<globalName>(%s)',
                    ')',
                    '|',
                    '(',
                        '?<namespacedName>(',
                            '%1$s(',
                                '\\\\%1$s',
                            ')*',
                            '\\\\%1$s',
                        ')',
                    ')',
                    '|',
                    '(',
                        '(',
                            '?<scope>(parent|self)',
                        ')',
                        '::',
                        '(',
                            '?<scopedName>(%1$s)',
                        ')',
                    ')',
                    '|',
                    '(',
                        '\\\\?',
                        '(',
                            '?<className>(%1$s(\\\\%1$s)*)',
                        ')',
                        '::',
                        '(',
                            '?<classConstantName>(%1$s)',
                        ')',
                    ')',
                ')',
                '$',
                '/',
            ]),
            self::getPHPClassNameRegexInner(),
        );
    }

    /**
     * @see https://www.php.net/manual/en/language.variables.basics.php
     */
    public static function getPHPClassNameRegexInner(): string
    {
        return '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
    }

    protected bool $isRenderingTypes = true;

    protected bool $isWrappingInClassName = true;

    protected ReflectionTypeFormatter $reflectionTypeFormatter;

    public function __construct()
    {
        $this->reflectionTypeFormatter = (new ReflectionTypeFormatter())->withIsWrappingInClassName(false);
    }

    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof ReflectionParameter); // Make phpstan happy

        $str = '';

        if ($this->isRenderingTypes() && $object->getType()) {
            $str = $this->getReflectionTypeFormatter()->format($caster, $object->getType()) . ' ';

            if ($object->isVariadic()) {
                $str .= '...';
            }
        }

        $str .= '$' . $object->getName();

        if ($object->isDefaultValueAvailable()) {
            $str .= ' = ' . $this->formatDefaultValue($caster, $object);
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

    /**
     * @throws RuntimeException
     */
    public function formatDefaultValue(CasterInterface $caster, ReflectionParameter $reflectionParameter): string
    {
        try {
            if (false === $reflectionParameter->isDefaultValueAvailable()) {
                throw new RuntimeException(sprintf(
                    'Parameter $%s does not have a default value',
                    $reflectionParameter->getName(),
                ));
            }

            /** @var string|null $defaultValueConstantName */
            $defaultValueConstantName = $reflectionParameter->getDefaultValueConstantName();

            if ($reflectionParameter->isDefaultValueConstant() && is_string($defaultValueConstantName)) {
                preg_match(
                    self::getDefaultValueConstantRegex(),
                    $defaultValueConstantName,
                    $match,
                );

                if (!$match) {
                    throw new RuntimeException(sprintf(
                        implode('', [
                            'Expects default value of parameter $%s - a constant - to match regular expression %s, but',
                            ' it does not. Found: %s',
                        ]),
                        $reflectionParameter->getName(),
                        escapeshellarg(self::getDefaultValueConstantRegex()),
                        Caster::getInternalInstance()->castTyped($defaultValueConstantName),
                    ));
                }

                foreach (['globalName', 'namespacedName'] as $key) {
                    if ($match[$key] ?: false) {
                        return '\\' . $match[$key];
                    }
                }

                if (
                    array_key_exists('scopedName', $match)
                    && '' !== $match['scopedName']
                    && array_key_exists('scope', $match)
                    && '' !== $match['scope']
                ) {
                    return sprintf(
                        '%s::%s',
                        $match['scope'],
                        $match['scopedName'],
                    );
                }

                if (
                    array_key_exists('className', $match)
                    && '' !== $match['className']
                    && array_key_exists('classConstantName', $match)
                    && '' !== $match['classConstantName']
                ) {
                    return sprintf(
                        '\\%s::%s',
                        $match['className'],
                        $match['classConstantName'],
                    );
                }
            }

            return $caster->cast($reflectionParameter->getDefaultValue());
        } catch (Throwable $t) {
            $functionText = '';

            if ($reflectionParameter->getDeclaringClass()) {
                $isStatic = false;

                if ($reflectionParameter->getDeclaringFunction() instanceof ReflectionMethod) {
                    $isStatic = $reflectionParameter->getDeclaringFunction()->isStatic();
                }

                $functionText = sprintf(
                    'method %s%s%s',
                    Caster::makeNormalizedClassName($reflectionParameter->getDeclaringClass()),
                    ($isStatic ? '::' : '->'),
                    $reflectionParameter->getDeclaringFunction()->getName(),
                );
            } else {
                $functionText = sprintf(
                    'function \\%s',
                    $reflectionParameter->getDeclaringFunction()->getName(),
                );
            }

            throw new RuntimeException(
                sprintf(
                    implode('', [
                        'A problem was encountered for argument $reflectionParameter, having the parameter name $%s',
                        ' in %s',
                    ]),
                    $reflectionParameter->getName(),
                    $functionText,
                ),
                0,
                $t,
            );
        }
    }

    public function getReflectionTypeFormatter(): ReflectionTypeFormatter
    {
        return $this->reflectionTypeFormatter;
    }

    public function isHandling(object $object): bool
    {
        return ($object instanceof ReflectionParameter);
    }

    public function isRenderingTypes(): bool
    {
        return $this->isRenderingTypes;
    }

    public function isWrappingInClassName(): bool
    {
        return $this->isWrappingInClassName;
    }

    /**
     * Returns a clone.
     */
    public function withIsRenderingTypes(bool $isRenderingTypes): self
    {
        $clone = clone $this;
        $clone->isRenderingTypes = $isRenderingTypes;

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
    public function withReflectionTypeFormatter(ReflectionTypeFormatter $reflectionTypeFormatter): self
    {
        $clone = clone $this;
        $clone->reflectionTypeFormatter = $reflectionTypeFormatter;

        return $clone;
    }
}
