<?php

declare(strict_types=1);

namespace Eboreum\Caster;

use Eboreum\Caster\Caster\Context;
use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Collection\Formatter\EnumFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Common\DataType\String_\Character;
use Eboreum\Caster\Contract\Caster\ContextInterface;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Contract\CharacterEncodingInterface;
use Eboreum\Caster\Contract\CharacterInterface;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;
use Eboreum\Caster\Contract\Formatter\EnumFormatterInterface;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;
use Eboreum\Caster\Contract\Formatter\StringFormatterInterface;
use Eboreum\Caster\Exception\CasterException;
use Eboreum\Caster\Formatter\DefaultArrayFormatter;
use Eboreum\Caster\Formatter\DefaultEnumFormatter;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use Eboreum\Caster\Formatter\DefaultResourceFormatter;
use Eboreum\Caster\Formatter\DefaultStringFormatter;
use Eboreum\Caster\Formatter\Object_\DebugIdentifierAttributeInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionAttributeFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionClassFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionMethodFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionPropertyFormatter;
use Eboreum\Caster\Formatter\Object_\ReflectionTypeFormatter;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use ReflectionType;
use Throwable;

use function addcslashes;
use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function array_walk;
use function assert;
use function count;
use function Eboreum\Caster\functions\is_enum;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_resource;
use function is_string;
use function mb_strlen;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function preg_split;
use function spl_object_hash;
use function sprintf;
use function str_repeat;
use function strcspn;
use function strval;
use function substr_replace;
use function trim;
use function uasort;

class Caster implements CasterInterface
{
    private static ?Caster $instance = null;

    /**
     * The instance used internally by this instance of Caster.
     */
    private static ?Caster $internalInstance = null;

    public static function getInstance(): Caster
    {
        if (null === self::$instance) {
            self::$instance = self::create();
        }

        return self::$instance;
    }

    /**
     * An instance meant for use internally within this library (eboreum/caster).
     */
    public static function getInternalInstance(): Caster
    {
        if (null === self::$internalInstance) {
            self::$internalInstance = self::getInstance();

            self::$internalInstance = self::$internalInstance->withCustomObjectFormatterCollection(
                new ObjectFormatterCollection([
                    new DebugIdentifierAttributeInterfaceFormatter(),
                    new TextuallyIdentifiableInterfaceFormatter(),
                ]),
            );
        }

        return self::$internalInstance;
    }

    public static function create(?CharacterEncodingInterface $characterEncoding = null): self
    {
        if (null === $characterEncoding) {
            $characterEncoding = CharacterEncoding::getInstance();
        }

        return new self($characterEncoding);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public static function makeNormalizedClassName(ReflectionClass $reflectionClass): string
    {
        if ($reflectionClass->isAnonymous()) {
            assert(is_string($reflectionClass->getFileName())); // Make phpstan happy

            $pretext = 'class';

            if ($reflectionClass->getParentClass()) {
                $pretext = '\\' . $reflectionClass->getParentClass()->getName();
            }

            return sprintf(
                '%s@anonymous/in/%s:%d',
                $pretext,
                preg_replace(
                    '/^\//',
                    '',
                    $reflectionClass->getFileName(),
                ),
                $reflectionClass->getStartLine(),
            );
        }

        return sprintf(
            '\\%s',
            $reflectionClass->getName(),
        );
    }

    /**
     * The character encoding to be used within the caster.
     */
    protected CharacterEncodingInterface $characterEncoding;

    /**
     * The current depth reached when formatting an array or an object.
     */
    protected PositiveInteger $depthCurrent;

    /**
     * The maximum depth a formatter is allowed to reach when formatting an array or an object.
     *
     * Once this limit is reached, the text "** OMITTED **" is displayed instead.
     */
    protected PositiveInteger $depthMaximum;

    /**
     * The maximum number of elements in an array which will be displayed. Upon exceeding this limit, a text
     * "n more element(s)" will be displayed instead.
     */
    protected UnsignedInteger $arraySampleSize;

    /**
     * The maximum number of characters in a string, which will be displayed, given the provided character encoding.
     * Upon exceeding this limit, an ellipsis will be shown and " (sample)" will be appended.
     */
    protected UnsignedInteger $stringSampleSize;

    /**
     * The character used to quote strings.
     */
    protected CharacterInterface $stringQuotingCharacter;

    /**
     * The ellipsis characters used to denote additional, but not visible content is present (like "...").
     */
    protected string $sampleEllipsis;

    /**
     * The message to be displayed instead of type and value when either a parameter has the #[\SensitiveParameter]
     * attribute or a class property has the #[\Eboreum\Caster\Attribute\SensitiveProperty] attribute.
     */
    protected string $sensitiveMessage = CasterInterface::SENSITIVE_MESSAGE_DEFAULT;

    /**
     * When `true`, ASCII control characters (including new line feed (\n) and carriage return (\r)) in strings must be
     * converted to their equivalent hex annotation. Said control characters include [\x00-\x1f] and \x7f. Example: A
     * new line (\n or \x0a) will appear as "\x0a".
     *
     * When `false`, no conversions occur. This may cause strings to appear in binary, e.g. when a string contains the
     * null byte (\x00) character.
     *
     * Notice: Any calculation of string length MUST be performed BEFORE the conversion is performed and AFTER escaping.
     */
    protected bool $isConvertingASCIIControlCharactersToHexAnnotationInStrings = false;

    /**
     * When `true`, the type of a value will be prepended in parenthesis.
     */
    protected bool $isPrependingType = false;

    /**
     * When `true`, arrays and strings will - when exceeding their respective sample size limits - because truncated,
     * and only a sample will be displayed.
     */
    protected bool $isMakingSamples = true;

    /**
     * When `true`, array, object, function/method arguments, etc. will be text wrapped, potentially improving
     * readability, especially of large and/or multi-level array/objects. Otherwise, no wrapping will occur.
     */
    protected bool $isWrapping = false;

    /**
     * The characters to be used for indentation when wrapping (see property $isWrapping) is enabled.
     */
    protected string $wrappingIndentationCharacters = '    ';

    protected DefaultStringFormatter $defaultStringFormatter;

    protected DefaultArrayFormatter $defaultArrayFormatter;

    protected DefaultEnumFormatter $defaultEnumFormatter;

    protected DefaultObjectFormatter $defaultObjectFormatter;

    protected DefaultResourceFormatter $defaultResourceFormatter;

    /**
     * A character used to mask out sbustrings in text, based on strings in the encrypted string collection.
     */
    protected CharacterInterface $maskingCharacter;

    /**
     * The number of times the masking character is repeated.
     */
    protected PositiveInteger $maskingStringLength;

    /** @var EncryptedStringCollection<EncryptedString> */
    protected EncryptedStringCollection $maskedEncryptedStringCollection;

    /** @var ArrayFormatterCollection<ArrayFormatterInterface> */
    protected ArrayFormatterCollection $customArrayFormatterCollection;

    /** @var EnumFormatterCollection<EnumFormatterInterface> */
    protected EnumFormatterCollection $customEnumFormatterCollection;

    /** @var ObjectFormatterCollection<ObjectFormatterInterface> */
    protected ObjectFormatterCollection $customObjectFormatterCollection;

    /** @var ResourceFormatterCollection<ResourceFormatterInterface> */
    protected ResourceFormatterCollection $customResourceFormatterCollection;

    /** @var StringFormatterCollection<StringFormatterInterface> */
    protected StringFormatterCollection $customStringFormatterCollection;

    /**
     * Used to determine the object context and to prevent cyclic object referencing.
     */
    protected ContextInterface $context;

    public function __construct(CharacterEncodingInterface $characterEncoding)
    {
        $this->characterEncoding = $characterEncoding;
        $this->depthCurrent = new PositiveInteger(1);
        $this->depthMaximum = new PositiveInteger(CasterInterface::DEPTH_MAXIMUM_DEFAULT);
        $this->arraySampleSize = new UnsignedInteger(CasterInterface::ARRAY_SAMPLE_SIZE_DEFAULT);
        $this->stringSampleSize = new UnsignedInteger(CasterInterface::STRING_SAMPLE_SIZE_DEFAULT);
        $this->stringQuotingCharacter = new Character(CasterInterface::STRING_QUOTING_CHARACTER_DEFAULT);
        $this->sampleEllipsis = CasterInterface::SAMPLE_ELLIPSIS_DEFAULT;
        $this->defaultStringFormatter = new DefaultStringFormatter();
        $this->defaultArrayFormatter = new DefaultArrayFormatter();
        $this->defaultEnumFormatter = new DefaultEnumFormatter();
        $this->defaultObjectFormatter = new DefaultObjectFormatter();
        $this->defaultResourceFormatter = new DefaultResourceFormatter();
        $this->maskingCharacter = new Character('*', $characterEncoding);
        $this->maskingStringLength = new PositiveInteger(6);
        $this->maskedEncryptedStringCollection = new EncryptedStringCollection();
        $this->customArrayFormatterCollection = new ArrayFormatterCollection();
        $this->customEnumFormatterCollection = new EnumFormatterCollection();
        $this->customObjectFormatterCollection = new ObjectFormatterCollection();
        $this->customResourceFormatterCollection = new ResourceFormatterCollection();
        $this->customStringFormatterCollection = new StringFormatterCollection();
        $this->context = new Context();
    }

    public function __clone()
    {
        $this->defaultArrayFormatter = clone $this->defaultArrayFormatter;
        $this->defaultEnumFormatter = clone $this->defaultEnumFormatter;
        $this->defaultObjectFormatter = clone $this->defaultObjectFormatter;
        $this->defaultResourceFormatter = clone $this->defaultResourceFormatter;
        $this->defaultStringFormatter = clone $this->defaultStringFormatter;
        $this->maskedEncryptedStringCollection = clone $this->maskedEncryptedStringCollection;
        $this->customArrayFormatterCollection = clone $this->customArrayFormatterCollection;
        $this->customEnumFormatterCollection = clone $this->customEnumFormatterCollection;
        $this->customObjectFormatterCollection = clone $this->customObjectFormatterCollection;
        $this->customResourceFormatterCollection = clone $this->customResourceFormatterCollection;
        $this->customStringFormatterCollection = clone $this->customStringFormatterCollection;
    }

    /**
     * @param mixed $value
     */
    public function cast($value): string // phpcs:ignore
    {
        $return = null;

        if (null === $value) {
            if ($this->isPrependingType()) {
                return '(null) null';
            }

            return 'null';
        }

        if (is_bool($value)) {
            $return = ($value ? 'true' : 'false');

            if ($this->isPrependingType()) {
                $return = sprintf(
                    '(bool) %s',
                    $return,
                );
            }

            return $return;
        }

        if (is_int($value) || is_float($value)) { // Cannot use `is_numeric`, because this accepts numeric strings
            $return = strval($value);

            if ($this->isPrependingType()) {
                $return = sprintf(
                    '(%s) %s',
                    (
                        is_int($value)
                        ? 'int'
                        : 'float' // gettype on a float value will return "double"
                    ),
                    $return,
                );
            }

            return $return;
        }

        if (is_string($value)) {
            $valueMasked = $this->maskString($value);

            foreach ($this->customStringFormatterCollection as $stringFormatter) {
                $return = $stringFormatter->format($this, $valueMasked);

                if (is_string($return)) {
                    break;
                }
            }

            if (null === $return) {
                $return = strval($this->getDefaultStringFormatter()->format($this, $valueMasked));
            }

            if ($value !== $valueMasked) {
                $return .= ' (masked)';
            }

            if ($this->isPrependingType()) {
                $return = sprintf(
                    '(string(%d)) %s',
                    mb_strlen($valueMasked, (string)$this->getCharacterEncoding()),
                    $return,
                );
            }

            return $return;
        }

        if (is_object($value)) {
            $caster = $this;
            $isEnum = is_enum($value);
            $typePrefixText = ($isEnum ? 'enum' : 'object');

            if ($caster->getContext()->hasVisitedObject($value)) {
                $return = $caster->getRecursionMessage($value);

                if ($caster->isPrependingType()) {
                    $return = sprintf(
                        '(%s) %s',
                        $typePrefixText,
                        $return
                    );
                }

                return $return;
            }

            if ($caster->getDepthCurrent()->toInteger() > $caster->getDepthMaximum()->toInteger()) {
                $return = sprintf(
                    '%s: %s',
                    self::makeNormalizedClassName(new ReflectionObject($value)),
                    $caster->getOmittedMaximumDepthOfXReachedMessage(),
                );

                if ($caster->isPrependingType()) {
                    $return = sprintf(
                        '(%s) %s',
                        $typePrefixText,
                        $return
                    );
                }

                return $return;
            }

            $caster = $caster->withContext(
                $caster->getContext()->withAddedVisitedObject($value)
            );

            $caster = $caster->withDepthCurrent(
                new PositiveInteger($caster->getDepthCurrent()->toInteger() + 1)
            );

            if ($isEnum) {
                foreach ($caster->customEnumFormatterCollection as $enumFormatter) {
                    $return = $enumFormatter->format($caster, $value);

                    if (is_string($return)) {
                        break;
                    }
                }

                if (null === $return) {
                    $return = strval($caster->getDefaultEnumFormatter()->format($caster, $value));
                }
            } else {
                foreach ($caster->customObjectFormatterCollection as $objectFormatter) {
                    $return = $objectFormatter->format($caster, $value);

                    if (is_string($return)) {
                        break;
                    }
                }

                if (null === $return) {
                    $return = strval($caster->getDefaultObjectFormatter()->format($caster, $value));
                }
            }

            if ($caster->isPrependingType()) {
                $return = sprintf(
                    '(%s) %s',
                    $typePrefixText,
                    $return,
                );
            }

            if ($caster->isWrapping()) {
                $lines = preg_split('/(\r?\n|\r)/', $return);

                assert(is_array($lines));

                $lines = array_values($lines);

                if ($this->getDepthCurrent()->toInteger() > 1) {
                    array_walk($lines, static function (string &$line, int $index) use ($caster): void {
                        if (0 === $index) {
                            /**
                             * First element is not being indented, because it is on the right side of an equal sign and
                             * should appear following that equal sign – not on a new line.
                             */
                            return;
                        }

                        $line = $caster->getWrappingIndentationCharacters() . $line;
                    });
                }

                $return = implode("\n", $lines);
            }

            return $return;
        }

        if (is_array($value)) {
            $caster = $this;

            if ($caster->getDepthCurrent()->toInteger() > $caster->getDepthMaximum()->toInteger()) {
                $return = sprintf(
                    '[%s] %s',
                    $caster->getSampleEllipsis(),
                    $caster->getOmittedMaximumDepthOfXReachedMessage(),
                );

                if ($caster->isPrependingType()) {
                    $return = sprintf(
                        '(array(%d)) %s',
                        count($value),
                        $return,
                    );
                }

                return $return;
            }

            $caster = $caster->withDepthCurrent(
                new PositiveInteger($caster->getDepthCurrent()->toInteger() + 1)
            );

            foreach ($caster->customArrayFormatterCollection as $arrayFormatter) {
                $return = $arrayFormatter->format($caster, $value);

                if (is_string($return)) {
                    break;
                }
            }

            if (null === $return) {
                $return = strval($caster->getDefaultArrayFormatter()->format($caster, $value));
            }

            if ($caster->isPrependingType()) {
                $return = sprintf(
                    '(array(%d)) %s',
                    count($value),
                    $return,
                );
            }

            if ($caster->isWrapping()) {
                $lines = preg_split('/(\r?\n|\r)/', $return);

                assert(is_array($lines));

                $lines = array_values($lines);

                if ($this->getDepthCurrent()->toInteger() > 1) {
                    array_walk($lines, static function (string &$line, int $index) use ($caster): void {
                        if (0 === $index) {
                            /**
                             * First element is not being indented, because it is on the right side of an equal sign and
                             * should appear following that equal sign – not on a new line.
                             */
                            return;
                        }

                        $line = $caster->getWrappingIndentationCharacters() . $line;
                    });
                }

                $return = implode("\n", $lines);
            }

            return $return;
        }

        assert(is_resource($value)); // Make phpstan happy

        foreach ($this->customResourceFormatterCollection as $resourceFormatter) {
            $return = $resourceFormatter->format($this, new Resource_($value));

            if (is_string($return)) {
                break;
            }
        }

        if (null === $return) {
            $return = strval($this->getDefaultResourceFormatter()->format($this, new Resource_($value)));
        }

        if ($this->isPrependingType()) {
            $return = sprintf(
                '(resource) %s',
                $return,
            );
        }

        return $return;
    }

    /**
     * Convenience method for casting a ReflectionAttribute to a string, rendering full class namespace and potential
     * arguments.
     *
     * @see https://www.php.net/manual/en/class.reflectionattribute.php
     *
     * @param ReflectionAttribute<object> $reflectionAttribute
     */
    public function castReflectionAttributeToString(ReflectionAttribute $reflectionAttribute): string
    {
        $formatter = new ReflectionAttributeFormatter();
        $formatter = $formatter->withIsWrappingInClassName(false);
        $formatted = $formatter->format($this, $reflectionAttribute);

        Assertion::assertIsString($formatted);
        assert(is_string($formatted));

        return $formatted;
    }

    /**
     * Convenience method for casting a ReflectionClass to a string, rendering full class namespace and an optional type
     * prefix (class, enum, interface, trait).
     *
     * @see https://www.php.net/manual/en/class.reflectionclass.php
     *
     * @param ReflectionClass<object> $reflectionClass
     */
    public function castReflectionClassToString(ReflectionClass $reflectionClass): string
    {
        $formatter = new ReflectionClassFormatter();
        $formatter = $formatter->withIsWrappingInClassName(false);
        $formatted = $formatter->format($this, $reflectionClass);

        Assertion::assertIsString($formatted);
        assert(is_string($formatted));

        return $formatted;
    }

    /**
     * Convenience method for casting a ReflectionMethod to a string, rendering full class name space, method name, and
     * all of the method's parameters.
     *
     * @see https://www.php.net/manual/en/class.reflectionmethod.php
     */
    public function castReflectionMethodToString(ReflectionMethod $reflectionMethod): string
    {
        $formatter = new ReflectionMethodFormatter();
        $formatter = $formatter->withIsWrappingInClassName(false);
        $formatted = $formatter->format($this, $reflectionMethod);

        Assertion::assertIsString($formatted);
        assert(is_string($formatted));

        return $formatted;
    }

    /**
     * Convenience method for casting a ReflectionProperty to a string, rendering full class name space, property name,
     * and optionally the property's type.
     *
     * @see https://www.php.net/manual/en/class.reflectionproperty.php
     */
    public function castReflectionPropertyToString(ReflectionProperty $reflectionProperty): string
    {
        $formatter = new ReflectionPropertyFormatter();
        $formatter = $formatter->withIsWrappingInClassName(false);
        $formatted = $formatter->format($this, $reflectionProperty);

        Assertion::assertIsString($formatted);
        assert(is_string($formatted));

        return $formatted;
    }

    /**
     * Convenience method for casting a ReflectionType to a string, normalizing all classish (class, enum, interface,
     * trait) references.
     *
     * @see https://www.php.net/manual/en/class.reflectiontype.php
     */
    public function castReflectionTypeToString(ReflectionType $reflectionType): string
    {
        $formatter = new ReflectionTypeFormatter();
        $formatter = $formatter->withIsWrappingInClassName(false);
        $formatted = $formatter->format($this, $reflectionType);

        Assertion::assertIsString($formatted);
        assert(is_string($formatted));

        return $formatted;
    }

    /**
     * @param mixed $value
     */
    public function castTyped($value): string // phpcs:ignore
    {
        return $this->withIsPrependingType(true)->cast($value);
    }

    public function escape(string $str): string
    {
        $escapee = '\\';

        if ((string)$this->stringQuotingCharacter !== $escapee) {
            $escapee .= (string)$this->stringQuotingCharacter;
        }

        return addcslashes($str, $escapee);
    }

    public function maskString(string $str): string
    {
        if (false === $this->maskedEncryptedStringCollection->isEmpty()) {
            $uasortMaskedStrings = function (string $a, string $b) {
                return (
                    (-1)
                    * (
                        mb_strlen($a, (string)$this->getCharacterEncoding())
                        <=>
                        mb_strlen($b, (string)$this->getCharacterEncoding())
                    )
                );
            };

            $maskedStrings = array_filter(
                array_map(
                    static function (EncryptedString $encryptedString) {
                        return $encryptedString->decrypt();
                    },
                    $this->maskedEncryptedStringCollection->toArray(),
                ),
                function (string $s) {
                    return mb_strlen($s, (string)$this->getCharacterEncoding()) > 0;
                }
            );

            uasort($maskedStrings, $uasortMaskedStrings);

            $overlappingMaskedStrings = [];

            /**
             * Masked strings can overlap each other, in which case we provide the product of the masked strings.
             */
            foreach ($maskedStrings as $i => $ma) {
                foreach ($maskedStrings as $j => $mb) {
                    if ($i === $j) {
                        continue;
                    }

                    $overlap = substr_replace($ma, $mb, strcspn($ma, $mb));

                    if (
                        $overlap === $ma
                        || $overlap === $mb
                        || $overlap === $ma . $mb
                        || $overlap === $mb . $ma
                    ) {
                        continue;
                    }

                    $overlappingMaskedStrings[] = $overlap;
                }
            }

            $maskedStrings = array_merge(
                $maskedStrings,
                $overlappingMaskedStrings,
            );

            if ($maskedStrings) {
                $maskedStrings = array_unique($maskedStrings);

                uasort($maskedStrings, $uasortMaskedStrings);

                $split = preg_split(
                    sprintf(
                        '/(%s)/',
                        implode(
                            '|',
                            array_map(
                                static function (string $maskedString) {
                                    return preg_quote($maskedString, '/');
                                },
                                $maskedStrings,
                            )
                        ),
                    ),
                    $str,
                    -1,
                );

                assert(is_array($split)); // Make phpstan happy

                if (count($split) > 1) {
                    $split = array_values($split);
                    $max = count($split) - 1;
                    $segments = [];
                    $maskingString = $this->getMaskingString();

                    for ($i = 0; $i <= $max; $i++) {
                        if ($i > 0) {
                            $segments[] = $maskingString;
                        }

                        $segments[] = $split[$i];
                    }

                    return implode('', $segments);
                }
            }
        }

        return $str;
    }

    public function quoteAndEscape(string $str): string
    {
        return implode(
            '',
            [
                (string)$this->stringQuotingCharacter,
                $this->escape($str),
                (string)$this->stringQuotingCharacter,
            ],
        );
    }

    public function sprintf(string $format, mixed ...$values): string
    {
        /** @var array<float|int|string> $valuesVariant */
        $valuesVariant = [];

        foreach ($values as $k => $v) {
            if (false === is_float($v) && false === is_int($v)) {
                $v = $this->cast($v);
            }

            $valuesVariant[$k] = $v;
        }

        return sprintf($format, ...$valuesVariant);
    }

    public function withArraySampleSize(UnsignedInteger $arraySampleSize): static
    {
        $clone = clone $this;
        $clone->arraySampleSize = $arraySampleSize;

        return $clone;
    }

    public function withCharacterEncoding(CharacterEncodingInterface $characterEncoding): static
    {
        $clone = clone $this;
        $clone->characterEncoding = $characterEncoding;

        return $clone;
    }

    public function withContext(ContextInterface $context): static
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function withCustomArrayFormatterCollection(ArrayFormatterCollection $customArrayFormatterCollection): static
    {
        $clone = clone $this;
        $clone->customArrayFormatterCollection = $customArrayFormatterCollection;

        return $clone;
    }

    public function withCustomEnumFormatterCollection(EnumFormatterCollection $customEnumFormatterCollection): static
    {
        $clone = clone $this;
        $clone->customEnumFormatterCollection = $customEnumFormatterCollection;

        return $clone;
    }

    public function withCustomObjectFormatterCollection(
        ObjectFormatterCollection $customObjectFormatterCollection,
    ): static {
        $clone = clone $this;
        $clone->customObjectFormatterCollection = $customObjectFormatterCollection;

        return $clone;
    }

    public function withCustomResourceFormatterCollection(
        ResourceFormatterCollection $customResourceFormatterCollection,
    ): static {
        $clone = clone $this;
        $clone->customResourceFormatterCollection = $customResourceFormatterCollection;

        return $clone;
    }

    public function withCustomStringFormatterCollection(
        StringFormatterCollection $customStringFormatterCollection,
    ): static {
        $clone = clone $this;
        $clone->customStringFormatterCollection = $customStringFormatterCollection;

        return $clone;
    }

    public function withDepthCurrent(PositiveInteger $depthCurrent): static
    {
        $clone = clone $this;
        $clone->depthCurrent = $depthCurrent;

        return $clone;
    }

    public function withDepthMaximum(PositiveInteger $depthMaximum): static
    {
        $clone = clone $this;
        $clone->depthMaximum = $depthMaximum;

        return $clone;
    }

    public function withIsConvertingASCIIControlCharactersToHexAnnotationInStrings(
        bool $isConvertingASCIIControlCharactersToHexAnnotationInStrings,
    ): static {
        $clone = clone $this;
        $clone->isConvertingASCIIControlCharactersToHexAnnotationInStrings = $isConvertingASCIIControlCharactersToHexAnnotationInStrings;

        return $clone;
    }

    public function withIsMakingSamples(bool $isMakingSamples): static
    {
        $clone = clone $this;
        $clone->isMakingSamples = $isMakingSamples;

        return $clone;
    }

    public function withIsPrependingType(bool $isPrependingType): static
    {
        $clone = clone $this;
        $clone->isPrependingType = $isPrependingType;

        return $clone;
    }

    public function withIsWrapping(bool $isWrapping): static
    {
        $clone = clone $this;
        $clone->isWrapping = $isWrapping;

        return $clone;
    }

    public function withMaskedEncryptedStringCollection(
        EncryptedStringCollection $maskedEncryptedStringCollection,
    ): static {
        $clone = clone $this;
        $clone->maskedEncryptedStringCollection = $maskedEncryptedStringCollection;

        return $clone;
    }

    public function withMaskingCharacter(CharacterInterface $maskingCharacter): static
    {
        $clone = clone $this;
        $clone->maskingCharacter = $maskingCharacter;

        return $clone;
    }

    public function withMaskingStringLength(PositiveInteger $maskingStringLength): static
    {
        $clone = clone $this;
        $clone->maskingStringLength = $maskingStringLength;

        return $clone;
    }

    public function withSampleEllipsis(string $sampleEllipsis): static
    {
        try {
            if ('' === $sampleEllipsis) {
                throw new CasterException(
                    'Argument $sampleEllipsis is an empty string, which is not allowed'
                );
            }

            if ('' === trim($sampleEllipsis)) {
                throw new CasterException(
                    sprintf(
                        implode('', [
                            'Argument $sampleEllipsis contains only white space characters, which is not allowed.',
                            ' Found: %s',
                        ]),
                        self::getInternalInstance()->castTyped($sampleEllipsis),
                    ),
                );
            }

            if (preg_match('/[\x00-\x1f]/', $sampleEllipsis)) {
                throw new CasterException(
                    sprintf(
                        implode('', [
                            'Argument $sampleEllipsis contains illegal characters.',
                            ' Found: %s',
                        ]),
                        self::getInternalInstance()->castTyped($sampleEllipsis),
                    ),
                );
            }

            $clone = clone $this;
            $clone->sampleEllipsis = $sampleEllipsis;
        } catch (Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$sampleEllipsis = %s',
                self::getInternalInstance()->castTyped($sampleEllipsis),
            );

            throw new CasterException(sprintf(
                'Failure in %s->%s(%s): %s',
                self::makeNormalizedClassName(new ReflectionObject($this)),
                __FUNCTION__,
                implode(', ', $argumentsAsStrings),
                self::getInternalInstance()->castTyped($this),
            ), 0, $t);
        }

        return $clone;
    }

    public function withSensitiveMessage(string $sensitiveMessage): static
    {
        $clone = clone $this;
        $clone->sensitiveMessage = $sensitiveMessage;

        return $clone;
    }

    public function withStringSampleSize(UnsignedInteger $stringSampleSize): static
    {
        $clone = clone $this;
        $clone->stringSampleSize = $stringSampleSize;

        return $clone;
    }

    public function withStringQuotingCharacter(CharacterInterface $stringQuotingCharacter): static
    {
        try {
            if ('\\' === (string)$stringQuotingCharacter) {
                throw new CasterException(sprintf(
                    'Argument $stringQuotingCharacter must not be a backslash, but it is. Found: %s',
                    self::getInternalInstance()->withIsPrependingType(true)->cast($stringQuotingCharacter),
                ));
            }

            $clone = clone $this;
            $clone->stringQuotingCharacter = $stringQuotingCharacter;
        } catch (Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$stringQuotingCharacter = %s',
                self::getInternalInstance()->castTyped($stringQuotingCharacter),
            );

            throw new CasterException(sprintf(
                'Failure in %s->%s(%s): %s',
                self::makeNormalizedClassName(new ReflectionObject($this)),
                __FUNCTION__,
                implode(', ', $argumentsAsStrings),
                self::getInternalInstance()->castTyped($this),
            ), 0, $t);
        }

        return $clone;
    }

    public function getArraySampleSize(): UnsignedInteger
    {
        return $this->arraySampleSize;
    }

    public function getCharacterEncoding(): CharacterEncodingInterface
    {
        return $this->characterEncoding;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getCustomArrayFormatterCollection(): ArrayFormatterCollection
    {
        return $this->customArrayFormatterCollection;
    }

    public function getCustomEnumFormatterCollection(): EnumFormatterCollection
    {
        return $this->customEnumFormatterCollection;
    }

    public function getCustomObjectFormatterCollection(): ObjectFormatterCollection
    {
        return $this->customObjectFormatterCollection;
    }

    public function getCustomResourceFormatterCollection(): ResourceFormatterCollection
    {
        return $this->customResourceFormatterCollection;
    }

    public function getCustomStringFormatterCollection(): StringFormatterCollection
    {
        return $this->customStringFormatterCollection;
    }

    public function getDefaultArrayFormatter(): DefaultArrayFormatter
    {
        return $this->defaultArrayFormatter;
    }

    public function getDefaultEnumFormatter(): DefaultEnumFormatter
    {
        return $this->defaultEnumFormatter;
    }

    public function getDefaultObjectFormatter(): DefaultObjectFormatter
    {
        return $this->defaultObjectFormatter;
    }

    public function getDefaultResourceFormatter(): DefaultResourceFormatter
    {
        return $this->defaultResourceFormatter;
    }

    public function getDefaultStringFormatter(): DefaultStringFormatter
    {
        return $this->defaultStringFormatter;
    }

    public function getDepthCurrent(): PositiveInteger
    {
        return $this->depthCurrent;
    }

    public function getDepthMaximum(): PositiveInteger
    {
        return $this->depthMaximum;
    }

    /**
     * Returns a clone of the collection to prevent outside interference.
     */
    public function getMaskedEncryptedStringCollection(): EncryptedStringCollection
    {
        return $this->maskedEncryptedStringCollection;
    }

    public function getMaskingCharacter(): CharacterInterface
    {
        return $this->maskingCharacter;
    }

    public function getMaskingString(): string
    {
        return str_repeat((string)$this->getMaskingCharacter(), $this->getMaskingStringLength()->toInteger());
    }

    public function getMaskingStringLength(): PositiveInteger
    {
        return $this->maskingStringLength;
    }

    public function getOmittedMaximumDepthOfXReachedMessage(): string
    {
        return sprintf(
            '** OMITTED ** (maximum depth of %d reached)',
            $this->getDepthMaximum()->toInteger(),
        );
    }

    public function getRecursionMessage(object $object): string
    {
        return sprintf(
            '** RECURSION ** (%s, %s)',
            self::makeNormalizedClassName(new ReflectionObject($object)),
            spl_object_hash($object),
        );
    }

    public function getSampleEllipsis(): string
    {
        return $this->sampleEllipsis;
    }

    public function getSensitiveMessage(): string
    {
        return $this->sensitiveMessage;
    }

    public function getStringSampleSize(): UnsignedInteger
    {
        return $this->stringSampleSize;
    }

    public function getStringQuotingCharacter(): CharacterInterface
    {
        return $this->stringQuotingCharacter;
    }

    public function getWrappingIndentationCharacters(): string
    {
        return $this->wrappingIndentationCharacters;
    }

    public function isConvertingASCIIControlCharactersToHexAnnotationInStrings(): bool
    {
        return $this->isConvertingASCIIControlCharactersToHexAnnotationInStrings;
    }

    public function isMakingSamples(): bool
    {
        return $this->isMakingSamples;
    }

    public function isPrependingType(): bool
    {
        return $this->isPrependingType;
    }

    public function isWrapping(): bool
    {
        return $this->isWrapping;
    }
}
