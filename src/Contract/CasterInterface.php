<?php

declare(strict_types=1);

namespace Eboreum\Caster\Contract;

use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Collection\Formatter\EnumFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Contract\Caster\ContextInterface;
use Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface;
use Eboreum\Caster\Contract\Formatter\EnumFormatterInterface;
use Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface;
use Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface;
use Eboreum\Caster\Contract\Formatter\StringFormatterInterface;
use Eboreum\Caster\EncryptedString;
use Eboreum\Caster\Exception\CasterException;
use Eboreum\Caster\Formatter\DefaultArrayFormatter;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;
use Eboreum\Caster\Formatter\DefaultResourceFormatter;
use Eboreum\Caster\Formatter\DefaultStringFormatter;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @inheritDoc
 *
 * Implementing class must be handling casting of any PHP value/data type to a human readable string.
 * Immutable. Use `with*` methods to generate copies.
 */
interface CasterInterface extends ImmutableObjectInterface
{
    public const ARRAY_SAMPLE_SIZE_DEFAULT = 3;
    public const DEPTH_MAXIMUM_DEFAULT = 3;
    public const SAMPLE_ELLIPSIS_DEFAULT = '...';
    public const SENSITIVE_MESSAGE_DEFAULT = '** REDACTED **';
    public const STRING_QUOTING_CHARACTER_DEFAULT = '"';
    public const STRING_SAMPLE_SIZE_DEFAULT = 1000;

    /**
     * Must always return the same instance.
     */
    public static function getInstance(): self;

    /**
     * Must return a new instance every time.
     */
    public static function create(?CharacterEncodingInterface $characterEncoding = null): self;

    public function __construct(CharacterEncodingInterface $characterEncoding);

    /**
     * Returns the spelled-out value. E.g. `true` will be output as "true", strings will be wrapped in quotes (like
     * `"foo"`), etc.
     *
     * To prepend information about the data type, first call `withIsPrependingType(true)` or simply call
     * `castTyped(...)` instead.
     */
    public function cast(mixed $value): string;

    /**
     * Convenience method for casting a ReflectionAttribute to a string, rendering full class namespace and potential
     * arguments.
     *
     * @see https://www.php.net/manual/en/class.reflectionattribute.php
     *
     * @param ReflectionAttribute<object> $reflectionAttribute
     */
    public function castReflectionAttributeToString(ReflectionAttribute $reflectionAttribute): string;

    /**
     * Convenience method for casting a ReflectionClass to a string, rendering full class namespace and an optional type
     * prefix (class, enum, interface, trait).
     *
     * @see https://www.php.net/manual/en/class.reflectionclass.php
     *
     * @param ReflectionClass<object> $reflectionClass
     */
    public function castReflectionClassToString(ReflectionClass $reflectionClass): string;

    /**
     * Convenience method for casting a ReflectionMethod to a string, rendering full class name space, method name, and
     * all of the method's parameters.
     *
     * @see https://www.php.net/manual/en/class.reflectionmethod.php
     */
    public function castReflectionMethodToString(ReflectionMethod $reflectionMethod): string;

    /**
     * Convenience method for casting a ReflectionProperty to a string, rendering full class name space, property name,
     * and optionally the property's type.
     *
     * @see https://www.php.net/manual/en/class.reflectionproperty.php
     */
    public function castReflectionPropertyToString(ReflectionProperty $reflectionProperty): string;

    /**
     * A convenience/proxy method for CasterInterface->withIsPrependingType(true)->cast(...).
     */
    public function castTyped(mixed $value): string;

    /**
     * Must escape backslashes and the quoting character with additional baskslashes.
     */
    public function escape(string $str): string;

    /**
     * Must mask out any and all masked strings (see `getMaskedEncryptedStringCollection`). Must also check if any
     * masked strings overlap, and then mask the product of all overlapping sensitive strings.
     * Masking must occur from longest to shortest sensitive strings.
     * Must use the value returned by `getMaskingString` for the masking.
     */
    public function maskString(string $str): string;

    /**
     * Must escape a string and wrap it in quoting characters. E.g. 'foo' will become '"foo"'.
     */
    public function quoteAndEscape(string $str): string;

    /**
     * Must have behavior very similar to that of the PHP core function `sprintf`. However, all values contained in the
     * $values argument, which are NOT float or int, must be wrapped using the `cast` method in the implementing class.
     *
     * Unlike the PHP core function `sprintf`, however, you may pass any data type – including null, booleans, arrays,
     * and objects (even objects, which do not implement the Stringable interface or has the `__toString` method) – in
     * this method, as these will be converted to their string represenations via the `cast` method.
     *
     * Method logic must NOT handle things like argnum, flags, width or precision, e.g. "%1$s", "%04s", "%2.3s".
     * Instead, format the values beforehand, pass them as strings, and use a simple "%s". The result of
     * "$this->cast('%04s', 'a')" will be `0"a"` (2x double quote takes up two of the characters) and NOT `"0a"` or
     * `"000a"`. Supporting these flags to make outputs like `"000a"`, would require a lot of reimplementing the
     * internals of the `sprintf` function, which we do not wish to do here. The use cases are very limited.
     *
     * @see https://www.php.net/manual/en/function.sprintf.php
     */
    public function sprintf(string $format, mixed ...$values): string;

    /**
     * Must set the maximum number of elements to be displayed in an array on a clone of the current instance.
     * Must return said clone.
     */
    public function withArraySampleSize(UnsignedInteger $arraySampleSize): static;

    /**
     * Must change the utilized character encoding on a clone of the current instance.
     * Must return said clone.
     */
    public function withCharacterEncoding(CharacterEncodingInterface $characterEncoding): static;

    /**
     * Must change the utilized context on a clone of the current instance.
     * Must return said clone.
     */
    public function withContext(ContextInterface $context): static;

    /**
     * Must change the utilized custom ArrayFormatterCollection on a clone of the current instance.
     * The order of elements in the collection is significant. Lower indexes will be handled first. However, if a given
     * formatter does not handle the provided value, it is passed on to the next formatter.
     * Must return said clone.
     *
     * @param ArrayFormatterCollection<ArrayFormatterInterface> $customArrayFormatterCollection
     */
    public function withCustomArrayFormatterCollection(
        ArrayFormatterCollection $customArrayFormatterCollection,
    ): static;

    /**
     * Must change the utilized custom EnumFormatterCollection on a clone of the current instance.
     * The order of elements in the collection is significant. Lower indexes will be handled first. However, if a given
     * formatter does not handle the provided value, it is passed on to the next formatter.
     * Must return said clone.
     *
     * @param EnumFormatterCollection<EnumFormatterInterface> $customEnumFormatterCollection
     */
    public function withCustomEnumFormatterCollection(EnumFormatterCollection $customEnumFormatterCollection): static;

    /**
     * Must change the utilized custom ObjectFormatterCollection on a clone of the current instance.
     * The order of elements in the collection is significant. Lower indexes will be handled first. However, if a given
     * formatter does not handle the provided value, it is passed on to the next formatter.
     * Must return said clone.
     *
     * @param ObjectFormatterCollection<ObjectFormatterInterface> $customObjectFormatterCollection
     */
    public function withCustomObjectFormatterCollection(
        ObjectFormatterCollection $customObjectFormatterCollection,
    ): static;

    /**
     * Must change the utilized custom ResourceFormatterCollection on a clone of the current instance.
     * The order of elements in the collection is significant. Lower indexes will be handled first. However, if a given
     * formatter does not handle the provided value, it is passed on to the next formatter.
     * Must return said clone.
     *
     * @param ResourceFormatterCollection<ResourceFormatterInterface> $customResourceFormatterCollection
     */
    public function withCustomResourceFormatterCollection(
        ResourceFormatterCollection $customResourceFormatterCollection,
    ): static;

    /**
     * Must change the utilized custom StringFormatterCollection on a clone of the current instance.
     * The order of elements in the collection is significant. Lower indexes will be handled first. However, if a given
     * formatter does not handle the provided value, it is passed on to the next formatter.
     * Must return said clone.
     *
     * @param StringFormatterCollection<StringFormatterInterface> $customStringFormatterCollection
     */
    public function withCustomStringFormatterCollection(
        StringFormatterCollection $customStringFormatterCollection,
    ): static;

    /**
     * Must change the current depth on a clone of the current instance. The current depth is used to determine how for
     * into an array or object structure, the casting logic has moved.
     * Must return said clone.
     */
    public function withDepthCurrent(PositiveInteger $depthCurrent): static;

    /**
     * Must change the maximum depth on a clone of the current instance. The maximum depth is used to determine depth
     * the casting logic is allowed to reach in arrays and object, after which contents will be omitted.
     * Must return said clone.
     */
    public function withDepthMaximum(PositiveInteger $depthMaximum): static;

    /**
     * Must change a clone of the current instance, instructing whether it on string should be converting ASCII control
     * character to their equivalent hex annotations.
     *
     * Must return said clone.
     */
    public function withIsConvertingASCIIControlCharactersToHexAnnotationInStrings(
        bool $isConvertingASCIIControlCharactersToHexAnnotationInStrings,
    ): static;

    /**
     * Must change a clone of the current instance, instructing whether it should make samples of values with large
     * amounts of data such as arrays with many elements and long text strings.
     * Must return said clone.
     */
    public function withIsMakingSamples(bool $isMakingSamples): static;

    /**
     * Must change a clone of the current instance, instructing whether it should prepend type (in parentheses) or not.
     * A prepended type is for instance the "(int)" part of: (int) 42
     * Must return said clone.
     */
    public function withIsPrependingType(bool $isPrependingType): static;

    /**
     * Must set the wrapping state on a clone of the current instance.
     *
     * Must return said clone.
     */
    public function withIsWrapping(bool $isWrapping): static;

    /**
     * Must change the utilized masked EncryptedStringCollection on a clone of the current instance.
     * Must return said clone.
     *
     * @param EncryptedStringCollection<EncryptedString> $maskedEncryptedStringCollection
     */
    public function withMaskedEncryptedStringCollection(
        EncryptedStringCollection $maskedEncryptedStringCollection,
    ): static;

    /**
     * Must change the utilized masked character on a clone of the current instance.
     * Must return said clone.
     */
    public function withMaskingCharacter(CharacterInterface $maskingCharacter): static;

    /**
     * Must change the masking string length, i.e. the number of times the masking character is repeated (see
     * `getMaskingCharacter` and `getMaskingString`), on a clone of the current instance.
     * Must return said clone.
     */
    public function withMaskingStringLength(PositiveInteger $maskingStringLength): static;

    /**
     * Must change the utilized sample ellipsis on a clone of the current instance.
     *
     * Argument $sampleEllipsis must not contain exclusively whitespace characters or ASCII characters \x00-\x1F.
     * If it does, a CasterException must be thrown.
     *
     * Must return said clone.
     *
     * @throws CasterException
     */
    public function withSampleEllipsis(string $sampleEllipsis): static;

    /**
     * Must change the string sample size on a clone of the current instance. The string sample size is the point after
     * which a string is truncated and turned into a sample.
     * Must return said clone.
     */
    public function withStringSampleSize(UnsignedInteger $stringSampleSize): static;

    /**
     * Must change the character used for quoting on a clone of the current instance.
     * Must return said clone.
     *
     * @param CharacterInterface $stringQuotingCharacter Must not be backlash. Otherwise, must throw a CasterException.
     *
     * @throws CasterException
     */
    public function withStringQuotingCharacter(CharacterInterface $stringQuotingCharacter): static;

    /**
     * Must return the number of elements in an array is being showed, before the array is truncated and a sample of it
     * is displayed.
     */
    public function getArraySampleSize(): UnsignedInteger;

    /**
     * Must return the character encoding which the caster and all formatters must utilize.
     */
    public function getCharacterEncoding(): CharacterEncodingInterface;

    /**
     * Must return the context for the current caster. Context is used to determine if an object has already been
     * visited by the casting logic and to prevent endless cyclic recursion.
     */
    public function getContext(): ContextInterface;

    /**
     * Must return the custom ArrayFormatterCollection, which is used for applying custom formattings for array.
     *
     * @return ArrayFormatterCollection<ArrayFormatterInterface>
     */
    public function getCustomArrayFormatterCollection(): ArrayFormatterCollection;

    /**
     * Must return the custom EnumFormatterCollection, which is used for applying custom formattings for enums.
     *
     * @return EnumFormatterCollection<EnumFormatterInterface>
     */
    public function getCustomEnumFormatterCollection(): EnumFormatterCollection;

    /**
     * Must return the custom ObjectFormatterCollection, which is used for applying custom formattings for objects.
     *
     * @return ObjectFormatterCollection<ObjectFormatterInterface>
     */
    public function getCustomObjectFormatterCollection(): ObjectFormatterCollection;

    /**
     * Must return the custom ResourceFormatterCollection, which is used for applying custom formattings for resources.
     *
     * @return ResourceFormatterCollection<ResourceFormatterInterface>
     */
    public function getCustomResourceFormatterCollection(): ResourceFormatterCollection;

    /**
     * Must return the custom StringFormatterCollection, which is used for applying custom formattings for strings.
     *
     * @return StringFormatterCollection<StringFormatterInterface>
     */
    public function getCustomStringFormatterCollection(): StringFormatterCollection;

    /**
     * Must return the default array formatter. This formatter is used when there are no customer array formatters or
     * if all custom array formatters have passed on to the next.
     */
    public function getDefaultArrayFormatter(): DefaultArrayFormatter;

    /**
     * Must return the default object formatter. This formatter is used when there are no customer object formatters or
     * if all custom object formatters have passed on to the next.
     */
    public function getDefaultObjectFormatter(): DefaultObjectFormatter;

    /**
     * Must return the default resource formatter. This formatter is used when there are no customer resource formatters
     * or if all custom resource formatters have passed on to the next.
     */
    public function getDefaultResourceFormatter(): DefaultResourceFormatter;

    /**
     * Must return the default string formatter. This formatter is used when there are no customer string formatters or
     * if all custom string formatters have passed on to the next.
     */
    public function getDefaultStringFormatter(): DefaultStringFormatter;

    /**
     * Must return the current depth for the caster. Current depth is used to determining how deep the caster logic has
     * dived into arrays and objects.
     */
    public function getDepthCurrent(): PositiveInteger;

    /**
     * Must return the maximum depth for the caster. Maximum depth is used as a limit for how deep the caster logic is
     * allowed to dive into arrays and objects.
     */
    public function getDepthMaximum(): PositiveInteger;

    /**
     * Must return the masked EncryptedStringCollection for the current instance. Masked strings are used to prevent
     * the displaying of sensitive information such as passwords, authentication tokens, and social security numbers.
     *
     * @return EncryptedStringCollection<EncryptedString>
     */
    public function getMaskedEncryptedStringCollection(): EncryptedStringCollection;

    /**
     * Must return the character which will be used to mask sensitive strings.
     */
    public function getMaskingCharacter(): CharacterInterface;

    /**
     * Must return the full masking string, based on the `getMaskingCharacter` and `getMaskingStringLength` methods.
     */
    public function getMaskingString(): string;

    /**
     * Must return the static length of the masking string. The length should always be static so that we do not reveal
     * information about the length of sensitive strings like passwords.
     */
    public function getMaskingStringLength(): PositiveInteger;

    /**
     * Must return a message describing that content has been omitted and how deep into an array and/or object the
     * casting logic has proceeded (see `getDepthMaximum`).
     */
    public function getOmittedMaximumDepthOfXReachedMessage(): string;

    /**
     * Must return a message describing that recursion -- or an endless cyclic reference -- on an object has occurred.
     * Utilize `getContext` to retrieve if an object has already been visited by the caster.
     */
    public function getRecursionMessage(object $object): string;

    /**
     * Must return the characters to be shown places where an ellipsis is used, e.g. "...".
     */
    public function getSampleEllipsis(): string;

    /**
     * The message to be displayed instead of type and value when either a parameter has the #[\SensitiveParameter]
     * attribute or a class property has the #[\Eboreum\Caster\Attribute\SensitiveProperty] attribute.
     */
    public function getSensitiveMessage(): string;

    /**
     * Must return how long a string must be before it is truncated and a sample of it is displayed.
     */
    public function getStringSampleSize(): UnsignedInteger;

    /**
     * Must return the character used for quotes, e.g. double quotes, apostrophe or backtick.
     */
    public function getStringQuotingCharacter(): CharacterInterface;

    /**
     * Must return the characters to be used for indentation when wrapping is enabled.
     *
     * Formatters should only apply one level of wrapping for its own shope. Instances of CasterInterface must handle
     * outer indentation.
     */
    public function getWrappingIndentationCharacters(): string;

    /**
     * Must return true when ASCII control characters (including new line feed (\n) and carriage return (\r)) in strings
     * must be converted to their equivalent hex annotation. Said control characters include [\x00-\x1f] and \x7f.
     * Example: A new line (\n or \x0a) must subsequently appear as "\x0a".
     *
     * When `false`, no conversions may occur. Without conversion, strings may appear in binary, e.g. when a string
     * contains the null byte (\x00) character, and on multiple different lines.
     *
     * Notice: Any calculation of string length MUST be performed BEFORE the conversion is performed and AFTER escaping.
     *
     * Otherwise, must return false.
     */
    public function isConvertingASCIIControlCharactersToHexAnnotationInStrings(): bool;

    /**
     * Must return true when data types susceptible to be coming samples (string and array) is being changed to samples
     * upon reaching their respective limits.
     *
     * Otherwise, must return false.
     */
    public function isMakingSamples(): bool;

    /**
     * Must return true when the data type is prepended (in parentheses) when using the casting logic. A prepended type
     * is for instance the "(int)" part of: (int) 42
     *
     * Otherwise, must return false.
     */
    public function isPrependingType(): bool;

    /**
     * Must return true when contents of arrays, objects, string, method arguments, etc. should be wrapped and thus have
     * their contents appear on several different lines.
     *
     * Otherwise, must return false.
     *
     * Wrapping may also cause indentations.
     *
     * Wrapping includes:
     *
     *   - Arrays: Keys and values will appear on individual lines.
     *   - Objects: Properties will appear on individual lines.
     *   - Function/Method arguments appearing on individual lines.
     *   - New lines in strings may be offset from the left because of indentation.
     */
    public function isWrapping(): bool;
}
