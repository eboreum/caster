<?php

declare(strict_types=1);

namespace Eboreum\Caster;

use Eboreum\Caster\Caster\Context;
use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
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
use Eboreum\Caster\Exception\CasterException;
use Eboreum\Caster\Formatter\DefaultArrayFormatter;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use Eboreum\Caster\Formatter\DefaultResourceFormatter;
use Eboreum\Caster\Formatter\DefaultStringFormatter;
use Eboreum\Caster\Formatter\Object_\DebugIdentifierAnnotationInterfaceFormatter;
use Eboreum\Caster\Formatter\Object_\TextuallyIdentifiableInterfaceFormatter;

/**
 * {@inheritDoc}
 */
class Caster implements CasterInterface
{
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
     * When `true`, the type of a value will be prepended in parenthesis.
     */
    protected bool $isPrependingType = false;

    /**
     * When `true`, arrays and strings will - when exceeding their respective sample size limits - because truncated,
     * and only a sample will be displayed.
     */
    protected bool $isMakingSamples = true;

    protected DefaultStringFormatter $defaultStringFormatter;

    protected DefaultArrayFormatter $defaultArrayFormatter;

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

    protected EncryptedStringCollection $maskedEncryptedStringCollection;

    protected ArrayFormatterCollection $customArrayFormatterCollection;

    protected ObjectFormatterCollection $customObjectFormatterCollection;

    protected ResourceFormatterCollection $customResourceFormatterCollection;

    protected StringFormatterCollection $customStringFormatterCollection;

    /**
     * Used to determine the object context and to prevent cyclic object referencing.
     */
    protected ContextInterface $context;

    private static ?Caster $instance = null;

    /**
     * The instance used internally by this instance of Caster.
     */
    private static ?Caster $internalInstance = null;

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
        $this->defaultObjectFormatter = new DefaultObjectFormatter();
        $this->defaultResourceFormatter = new DefaultResourceFormatter();
        $this->maskingCharacter = new Character('*', $characterEncoding);
        $this->maskingStringLength = new PositiveInteger(6);
        $this->maskedEncryptedStringCollection = new EncryptedStringCollection();
        $this->customArrayFormatterCollection = new ArrayFormatterCollection();
        $this->customObjectFormatterCollection = new ObjectFormatterCollection();
        $this->customResourceFormatterCollection = new ResourceFormatterCollection();
        $this->customStringFormatterCollection = new StringFormatterCollection();
        $this->context = new Context();
    }

    /**
     * {@inheritDoc}
     */
    public static function getInstance(): Caster
    {
        if (null === self::$instance) {
            self::$instance = static::create();
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
                new ObjectFormatterCollection(
                    new DebugIdentifierAnnotationInterfaceFormatter(),
                    new TextuallyIdentifiableInterfaceFormatter(),
                ),
            );
        }

        return self::$internalInstance;
    }

    /**
     * {@inheritDoc}
     */
    public static function create(?CharacterEncodingInterface $characterEncoding = null): Caster
    {
        if (null === $characterEncoding) {
            $characterEncoding = CharacterEncoding::getInstance();
        }

        return new self($characterEncoding);
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    public static function makeNormalizedClassName(\ReflectionClass $reflectionClass): string
    {
        if ($reflectionClass->isAnonymous()) {
            assert(is_string($reflectionClass->getFileName()));

            return sprintf(
                'class@anonymous/in/%s:%d',
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
     * {@inheritDoc}
     */
    public function cast($value): string
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

            if ($caster->getContext()->hasVisitedObject($value)) {
                $return = $caster->getRecursionMessage($value);

                if ($caster->isPrependingType()) {
                    $return = sprintf(
                        '(object) %s',
                        $return
                    );
                }

                return $return;
            }

            if ($caster->getDepthCurrent()->toInteger() > $caster->getDepthMaximum()->toInteger()) {
                $return = sprintf(
                    '%s: %s',
                    self::makeNormalizedClassName(new \ReflectionObject($value)),
                    $caster->getOmittedMaximumDepthOfXReachedMessage(),
                );

                if ($caster->isPrependingType()) {
                    $return = sprintf(
                        '(object) %s',
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

            foreach ($caster->customObjectFormatterCollection as $objectFormatter) {
                $return = $objectFormatter->format($caster, $value);

                if (is_string($return)) {
                    break;
                }
            }

            if (null === $return) {
                $return = strval($caster->getDefaultObjectFormatter()->format($caster, $value));
            }

            if ($caster->isPrependingType()) {
                $return = sprintf(
                    '(object) %s',
                    $return,
                );
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

            return $return;
        }

        assert(is_resource($value));

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
     * {@inheritDoc}
     */
    public function castTyped($value): string
    {
        return $this->withIsPrependingType(true)->cast($value);
    }

    /**
     * {@inheritDoc}
     */
    public function escape(string $str): string
    {
        $escapee = '\\';

        if ((string)$this->stringQuotingCharacter !== $escapee) {
            $escapee .= (string)$this->stringQuotingCharacter;
        }

        return addcslashes($str, $escapee);
    }

    /**
     * {@inheritDoc}
     */
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

                assert(is_array($split));

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

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function withArraySampleSize(UnsignedInteger $arraySampleSize): Caster
    {
        $clone = clone $this;
        $clone->arraySampleSize = $arraySampleSize;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withCharacterEncoding(CharacterEncodingInterface $characterEncoding): Caster
    {
        $clone = clone $this;
        $clone->characterEncoding = $characterEncoding;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withContext(ContextInterface $context): Caster
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withCustomArrayFormatterCollection(ArrayFormatterCollection $customArrayFormatterCollection): Caster
    {
        $clone = clone $this;
        $clone->customArrayFormatterCollection = $customArrayFormatterCollection;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withCustomObjectFormatterCollection(
        ObjectFormatterCollection $customObjectFormatterCollection
    ): Caster {
        $clone = clone $this;
        $clone->customObjectFormatterCollection = $customObjectFormatterCollection;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withCustomResourceFormatterCollection(
        ResourceFormatterCollection $customResourceFormatterCollection
    ): Caster {
        $clone = clone $this;
        $clone->customResourceFormatterCollection = $customResourceFormatterCollection;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withCustomStringFormatterCollection(
        StringFormatterCollection $customStringFormatterCollection
    ): Caster {
        $clone = clone $this;
        $clone->customStringFormatterCollection = $customStringFormatterCollection;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withDepthCurrent(PositiveInteger $depthCurrent): Caster
    {
        $clone = clone $this;
        $clone->depthCurrent = $depthCurrent;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withDepthMaximum(PositiveInteger $depthMaximum): Caster
    {
        $clone = clone $this;
        $clone->depthMaximum = $depthMaximum;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withIsMakingSamples(bool $isMakingSamples): Caster
    {
        $clone = clone $this;
        $clone->isMakingSamples = $isMakingSamples;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withIsPrependingType(bool $isPrependingType): Caster
    {
        $clone = clone $this;
        $clone->isPrependingType = $isPrependingType;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withMaskedEncryptedStringCollection(
        EncryptedStringCollection $maskedEncryptedStringCollection
    ): Caster {
        $clone = clone $this;
        $clone->maskedEncryptedStringCollection = $maskedEncryptedStringCollection;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withMaskingCharacter(CharacterInterface $maskingCharacter): Caster
    {
        $clone = clone $this;
        $clone->maskingCharacter = $maskingCharacter;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withMaskingStringLength(PositiveInteger $maskingStringLength): Caster
    {
        $clone = clone $this;
        $clone->maskingStringLength = $maskingStringLength;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withSampleEllipsis(string $sampleEllipsis): Caster
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
        } catch (\Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$sampleEllipsis = %s',
                self::getInternalInstance()->castTyped($sampleEllipsis),
            );

            throw new CasterException(sprintf(
                'Failure in %s->%s(%s): %s',
                self::makeNormalizedClassName(new \ReflectionObject($this)),
                __FUNCTION__,
                implode(', ', $argumentsAsStrings),
                self::getInternalInstance()->castTyped($this),
            ), 0, $t);
        }

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withStringSampleSize(UnsignedInteger $stringSampleSize): Caster
    {
        $clone = clone $this;
        $clone->stringSampleSize = $stringSampleSize;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withStringQuotingCharacter(CharacterInterface $stringQuotingCharacter): Caster
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
        } catch (\Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$stringQuotingCharacter = %s',
                self::getInternalInstance()->castTyped($stringQuotingCharacter),
            );

            throw new CasterException(sprintf(
                'Failure in %s->%s(%s): %s',
                self::makeNormalizedClassName(new \ReflectionObject($this)),
                __FUNCTION__,
                implode(', ', $argumentsAsStrings),
                self::getInternalInstance()->castTyped($this),
            ), 0, $t);
        }

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getArraySampleSize(): UnsignedInteger
    {
        return $this->arraySampleSize;
    }

    /**
     * {@inheritDoc}
     */
    public function getCharacterEncoding(): CharacterEncodingInterface
    {
        return $this->characterEncoding;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomArrayFormatterCollection(): ArrayFormatterCollection
    {
        return $this->customArrayFormatterCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomObjectFormatterCollection(): ObjectFormatterCollection
    {
        return $this->customObjectFormatterCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomResourceFormatterCollection(): ResourceFormatterCollection
    {
        return $this->customResourceFormatterCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomStringFormatterCollection(): StringFormatterCollection
    {
        return $this->customStringFormatterCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultArrayFormatter(): DefaultArrayFormatter
    {
        return $this->defaultArrayFormatter;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultObjectFormatter(): DefaultObjectFormatter
    {
        return $this->defaultObjectFormatter;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultResourceFormatter(): DefaultResourceFormatter
    {
        return $this->defaultResourceFormatter;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultStringFormatter(): DefaultStringFormatter
    {
        return $this->defaultStringFormatter;
    }

    /**
     * {@inheritDoc}
     */
    public function getDepthCurrent(): PositiveInteger
    {
        return $this->depthCurrent;
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function getMaskingCharacter(): CharacterInterface
    {
        return $this->maskingCharacter;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaskingString(): string
    {
        return str_repeat((string)$this->getMaskingCharacter(), $this->getMaskingStringLength()->toInteger());
    }

    /**
     * {@inheritDoc}
     */
    public function getMaskingStringLength(): PositiveInteger
    {
        return $this->maskingStringLength;
    }

    /**
     * {@inheritDoc}
     */
    public function getOmittedMaximumDepthOfXReachedMessage(): string
    {
        return sprintf(
            '** OMITTED ** (maximum depth of %d reached)',
            $this->getDepthMaximum()->toInteger(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRecursionMessage(object $object): string
    {
        return sprintf(
            '** RECURSION ** (%s, %s)',
            self::makeNormalizedClassName(new \ReflectionObject($object)),
            spl_object_hash($object),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getSampleEllipsis(): string
    {
        return $this->sampleEllipsis;
    }

    /**
     * {@inheritDoc}
     */
    public function getStringSampleSize(): UnsignedInteger
    {
        return $this->stringSampleSize;
    }

    /**
     * {@inheritDoc}
     */
    public function getStringQuotingCharacter(): CharacterInterface
    {
        return $this->stringQuotingCharacter;
    }

    /**
     * {@inheritDoc}
     */
    public function isMakingSamples(): bool
    {
        return $this->isMakingSamples;
    }

    /**
     * {@inheritDoc}
     */
    public function isPrependingType(): bool
    {
        return $this->isPrependingType;
    }

    public function __clone()
    {
        $this->defaultArrayFormatter = clone $this->defaultArrayFormatter;
        $this->defaultObjectFormatter = clone $this->defaultObjectFormatter;
        $this->defaultResourceFormatter = clone $this->defaultResourceFormatter;
        $this->defaultStringFormatter = clone $this->defaultStringFormatter;
        $this->maskedEncryptedStringCollection = clone $this->maskedEncryptedStringCollection;
        $this->customArrayFormatterCollection = clone $this->customArrayFormatterCollection;
        $this->customObjectFormatterCollection = clone $this->customObjectFormatterCollection;
        $this->customResourceFormatterCollection = clone $this->customResourceFormatterCollection;
        $this->customStringFormatterCollection = clone $this->customStringFormatterCollection;
    }
}
