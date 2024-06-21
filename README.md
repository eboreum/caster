Eboreum/Caster: A PHP type formatter
===============================

![license](https://img.shields.io/github/license/eboreum/caster?label=license)
![build](https://github.com/eboreum/caster/workflows/build/badge.svg?branch=main)
[![Code Coverage](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/kafoso/359b4a5b48d20766dad458350fd11269/raw/test-coverage__main.json)](https://github.com/eboreum/caster/actions)
[![PHPStan Level](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/kafoso/359b4a5b48d20766dad458350fd11269/raw/phpstan-level__main.json)](https://github.com/eboreum/caster/actions)

Cast any PHP value to a sensible, human readable string. Great for type-safe outputs, exception messages, transparency during debugging, and similar things. Also helps avoiding innate problems such as printing endless, circular referencing objects (endless recursion), limits the output for large arrays and long strings, and prevents (opt-in) the outputting of sensitive strings like passwords.

[comment]: # (The README.md is generated using `script/generate-readme.php`)


**Why use Eboreum/Caster instead things like [XDebug](https://xdebug.org/), [`symfony/var-dumper`](https://packagist.org/packages/symfony/var-dumper), and similar libraries?**

[XDebug](https://xdebug.org/), [`symfony/var-dumper`](https://packagist.org/packages/symfony/var-dumper), and similar libraries are meant for **development environments**.

**Eboreum/Caster** is meant for any and **all environments** (development, test, staging, production).

With Eboreum/Caster, you will be able to provide excellent information about all PHP values, which is great in both **debugging** and **failure scenarios**. Are the exception messages in your application lacklustre? Expand your options and displayed values greatly with Eboreum/Caster!

This package can be thought of as an extended version of the magic method `__debugInfo` ([https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo](https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo)). However, contrary to `__debugInfo`, where only the internals of the implementing class is used for building sensible debug information – with the occasional (often abominable) static method calls to other classes – Caster allows for much more variety, including custom formatters utilizing proper dependency injection.

Eboreum/Caster **gives you -- the developer -- the ultimate power** to control how output is handled, parsed and presented through opt-in utilization of custom formatters.

Lastly, you may provide a series of **sensitive text strings** like passwords, authentication tokens, social security numbers, and similar, preventing these from being output inside strings. Wouldn't want these to show up in error logs, emails, and what have you. Upon encountering sensitive strings, said sensitive substrings will be masked, instead showing a static length string replacement (like `******`).

<a name="requirements"></a>
# Requirements

```json
"php": "^8.1",
"ext-mbstring": "*",
"ext-openssl": "*"
```

For more information, see the [`composer.json`](composer.json) file.

# Installation

Via [Composer](https://getcomposer.org/) (https://packagist.org/packages/eboreum/caster):

    composer install eboreum/caster

Via GitHub:

    git clone git@github.com:eboreum/caster.git

# Fundamentals

## Type conversions to string

The data types are converted as illustrated in the table below.

|Type|Conversion logic|Example(s)|Note|
|---|---|---|---|
|Null|As is.|`null`| |
|Booleans|As is.|`true`<br>`false`| |
|Float numbers|As is.|`3.14`|Standard float-to-string conversion rounding will occur, as produced by `strval(3.14)`.|
|Integers|As is.|`42`| |
|Strings|As is or as a sample (substring).|`"foo"`<br>`"bar ..." (sample)`|If you wish to control how strings are presented or apply conditions, you may do so by providing an instance of `\Eboreum\Caster\Contract\Formatter\StringFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-string-formatter">further down</a>.|
|Arrays|As is or as a sample.|`[0 => "foo"]`<br><br>`[0 => "bar" ... and 9 more elements]`|**Sub-arrays**<br>By default, no sub-arrays are displayed; i.e. the depth is zero. However, a custom depth may be specified.<br>Sub-arrays with depth 0 (zero) may appear as such: `[0 => (array(1)) [...]]`<br>Sub-arrays with depth 1 may appear as such: `[0 => (array(1)) ["foo"]]`<br><br>**Sampling and sample size**<br>By default, a maximum of 3 elements are displayed, before the " ... and X more elements" message is displayed. This number is also customizible.<br><br>**Custom array-to-string conversion**<br>If you wish to customize how arrays are being converted to a string, you may do so by providing an instance of `\Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-array-formatter">further down</a>.|
|Objects|Class namespace with leading backslash.|`\stdClass`<br><br>`class@anonymous/in/foo/bar/baz.php:22`|Objects are rather complex types. As such, something sensible besides its class name cannot be reliably displayed. Not even using `__toString` or similar methods.<br><br>**Custom object-to-string conversion**<br>If you wish to customize how objects are being converted to a string, you may do so by providing an instance of `\Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-object-formatter">further down</a>.<br>This is especially useful for displaying relevant information in classes such as IDs in [Doctrine ORM entities](https://github.com/doctrine/orm).|
|Resource_|A text and the resource's ID.|`#Resource id #2`|Resources can be many different things. A file pointer, database connection, image canvas, etc. As such, only the bare minimum of information is displayed.<br><br>**Custom resource-to-string conversion**<br>If you wish to customize how resources are being converted to a string, you may do so by providing an instance of `\Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-resource-formatter">further down</a>.|

# Output examples

## Echo

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Caster;

$caster = Caster::create();

echo sprintf(
    "%s\n%s\n%s\n%s",
    $caster->cast(null),
    $caster->cast(true),
    $caster->cast('foo'),
    $caster->cast(new stdClass())
);

$caster = $caster->withIsPrependingType(true);

echo "\n\n";

echo sprintf(
    "%s\n%s\n%s\n%s",
    $caster->cast(null),
    $caster->cast(true),
    $caster->cast('foo'),
    $caster->cast(new stdClass())
);

```

**Output:**

```
null
true
"foo"
\stdClass

(null) null
(bool) true
(string(3)) "foo"
(object) \stdClass
```

## Exception

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Caster;

/**
 * @throws InvalidArgumentException
 */
function foo(mixed $value): void
{
    if (false === is_string($value) && false === is_int($value)) {
        throw new InvalidArgumentException(sprintf(
            'Expects argument $value to be a string or an integer. Found: %s',
            Caster::create()->castTyped($value),
        ));
    }
}

try {
    foo(['bar']);
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();
}

```

**Output:**

```php
Expects argument $value to be a string or an integer. Found: (array(1)) [(int) 0 => (string(3)) "bar"]
```

# Usage

`\Eboreum\Caster\Caster` is immutable. This is a great guard against tampering with the internals of the Caster class. However, a multitude of `with*` methods are supplied, allowing clones to be mutated.

It is recommended that you, in your own application, implement a `\My\Application\Caster`, which extends `\Eboreum\Caster\Caster` and overrides the `getInstance` method, from where you may gain full control over your own application's instance of the caster.

**Example:**

```php
<?php

declare(strict_types=1);

namespace My\Application;

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Caster as EboreumCaster;
use Eboreum\Caster\CharacterEncoding;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;

use function assert;
use function dirname;
use function json_encode;
use function sprintf;

class Caster extends EboreumCaster
{
    private static ?Caster $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self(CharacterEncoding::getInstance());

            $instance = self::$instance->withCustomArrayFormatterCollection(new ArrayFormatterCollection([
            new class extends AbstractArrayFormatter
                {
                public function format(CasterInterface $caster, array $array): ?string
                {
                    return 'I am an array!';
                }

                public function isHandling(array $array): bool
                {
                    return true;
                }
            },
            ]));

            assert($instance instanceof Caster);

            self::$instance = $instance;

            // Do more custom configuring before the instance is forever locked and returned
        }

        return self::$instance;
    }
}

echo sprintf(
    'Instances \\%s::getInstance() !== \\%s::getInstance(): %s',
    EboreumCaster::class,
    Caster::class,
    json_encode(EboreumCaster::getInstance() !== Caster::getInstance()),
) . "\n";

echo sprintf(
    'But \\%s::getInstance() === \\%s::getInstance() (same): %s',
    Caster::class,
    Caster::class,
    json_encode(Caster::getInstance() === Caster::getInstance()),
) . "\n";

```

**Output:**

```php
Instances \Eboreum\Caster\Caster::getInstance() !== \My\Application\Caster::getInstance(): true
But \My\Application\Caster::getInstance() === \My\Application\Caster::getInstance() (same): true

```

## The standard formatter

By default, `Eboreum\Caster\Caster::create()` returns a new instance every time. If you wish to re-use the same instance over and over, you have two options.

**Option 1:** Store it in a variable and use that. As such:

```php
<?php
use Eboreum\Caster\Caster;

$caster = Caster::create();
```

**Option 2:** Use `getInstance`.

For ease-of-use, you may retrieve the same instance by calling `\Eboreum\Caster\Caster::getInstance()`. As describe above, it is recommended you make you own `\My\Application\Caster::getInstance()` in your application or library.

### Use a real Dependency Injection Container

Alternatively, use an actual Dependency Injection Container (DIC) such as [Pimple](https://pimple.symfony.com/). However, this means you will have to pass around the dependencies everywhere you need them, which - from a SOLID perspective - is nice, but not always very practical.

## A custom basic formatter

You may customize the formatter to your specific needs, e.g. changing string sample size, array depth, or providing custom array and/or object formatters.

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Caster;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Common\DataType\String_\Character;

$caster = Caster::create();
$caster = $caster->withDepthMaximum(new PositiveInteger(2));
$caster = $caster->withArraySampleSize(new UnsignedInteger(3));
$caster = $caster->withStringSampleSize(new UnsignedInteger(4));
$caster = $caster->withStringQuotingCharacter(new Character('`'));

echo '$caster->getDepthMaximum()->toInteger(): ' . $caster->getDepthMaximum()->toInteger() . "\n";
echo '$caster->getArraySampleSize()->toInteger(): ' . $caster->getArraySampleSize()->toInteger() . "\n";
echo '$caster->getStringSampleSize()->toInteger(): ' . $caster->getStringSampleSize()->toInteger() . "\n";
echo '$caster->getStringQuotingCharacter(): ' . $caster->getStringQuotingCharacter() . "\n";

```

**Output:**

```php
$caster->getDepthMaximum()->toInteger(): 2
$caster->getArraySampleSize()->toInteger(): 3
$caster->getStringSampleSize()->toInteger(): 4
$caster->getStringQuotingCharacter(): `

```

## Type specific formatters

The following type specific formatters exist, which may help providing additional information. Especially useful for printing relevant information relating to an object.

Add formatters (immutably) to the `\Eboreum\Caster\Caster` using the `with*` methods (returns a clone).

|Data type|`\Eboreum\Caster\Caster` method|Interface|Note|
|---|---|---|---|
|`array`|`withCustomArrayFormatterCollection`|`\Eboreum\Caster\Contract\Formatter\ArrayFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-array-formatter">Custom array formatter</a> further down.|
|`object`|`withCustomObjectFormatterCollection`|`\Eboreum\Caster\Contract\Formatter\ObjectFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-object-formatter">Custom object formatter</a> further down.<br><br>**Notice:** This library ships with a series of ready-to-use object formatters. These may be found under `\Eboreum\Caster\Formatter\Object_`. Details [below](#usage--type-specific-formatters--included-object-formatters).|
|`resource`|`withCustomResourceFormatterCollection`|`\Eboreum\Caster\Contract\Formatter\ResourceFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-resource-formatter">Custom resource formatter</a> further down.|
|`string`|`withCustomStringFormatterCollection`|`\Eboreum\Caster\Contract\Formatter\StringFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-string-formatter">Custom string formatter</a> further down.|

Multiple custom formatters can be provided, such that they each handle only specific cases. Order is significant. The first element in the collection is handled first. You **must order** the collection elements, before passing them to `\Eboreum\Caster\Caster`.

Ultimately, all custom formatters fall back to their respective standard formatters.

<a name="usage--type-specific-formatters--included-object-formatters"></a>
### Included object formatters

The following object formatters are readily available. You may use them as-is or extend them, providing your own custom logic. Everything is very Open-closed Principle.

**Namespace:** `\Eboreum\Caster\Formatter\Object_`

|Class name|Description|Output example(s)|
|---|---|---|
|`DateIntervalFormatter`|Formats `\DateInterval` objects.|`\DateInterval {$y = 0, $m = 1, $d = 2, $h = 12, $i = 34, $s = 56, $f = 0, $weekday = 0, $weekday_behavior = 0, $first_last_day_of = 0, $invert = 0, $days = 33, $special_type = 0, $special_amount = 0, $have_weekday_relative = 0, $have_special_relative = 0}`|
|`DatePeriodFormatter`|Formats `\DatePeriod` objects.|`\DatePeriod (start: \DateTimeImmutable ("2020-01-01T00:00:00+00:00"), end: \DateTimeImmutable ("2020-01-01T00:00:00+00:00"), recurrences: null, interval: \DateInterval)`|
|`DateTimeInterfaceFormatter`|Formats `\DateTimeInterface` objects, appending ISO 8601 time in parenthesis.|`\DateTimeImmutable ("2019-01-01T00:00:00+00:00")`|
|`DebugIdentifierAttributeInterfaceFormatter`|Formats objects, which implement the interface `\Eboreum\Caster\Contract\DebugIdentifierAttributeInterface` .|`class@anonymous/in/foo/bar/baz.php:22 {$foo = 42} ($path = "/foo.php")`|
|`DirectoryFormatter`|Formats `\Directory` objects, as produced by `dir(__DIR__)`.|`\Directory ($path = "/foo.php")`|
|`PublicVariableFormatter`|Formats any object which has publicly accessible variables.|`\stdClass {$foo = "bar"}`|
|`SplFileInfoFormater`|Formats `\SplFileInfo` objects.|`\SplFileInfo ("/my/system/foo.txt")`|
|`TextuallyIdentifiableInterfaceFormatter`|Formats objects, which implement the interface `\Eboreum\Caster\Contract\TextuallyIdentifiableInterface`.|`\MyUserClass (USER.ID = 22)`|
|`ThrowableFormatter`|Formats instances of `\Throwable`.<br><br>**Caution:** The output is greatly simplified compared to properly dumping a `\Throwable` with stack trace and everything else.|`\RuntimeException {$code = 0, $file = "/foo.php", $line = 22, $message = "bar", $previous = null}`|

<a name="usage--type-specific-formatters--custom-array-formatter"></a>
### Custom array formatter

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ArrayFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;

$caster = Caster::create();
$caster = $caster->withCustomArrayFormatterCollection(new ArrayFormatterCollection([
    new class extends AbstractArrayFormatter
    {
        public function format(CasterInterface $caster, array $array): ?string
        {
            if (false === $this->isHandling($array)) {
                return null; // Pass on to next formatter or lastly DefaultArrayFormatter
            }

            if (1 === count($array)) {
                /*
                 * /!\ CAUTION /!\
                 * Do NOT do this in practice! You disable sensitive string masking.
                 */
                return print_r($array, true);
            }

            if (2 === count($array)) {
                return 'I am an array!';
            }

            if (3 === count($array)) {
                $array[0] = 'SURPRISE!';

                // Override and use DefaultArrayFormatter for rendering output
                return $caster->getDefaultArrayFormatter()->format($caster, $array);
            }

            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }

        public function isHandling(array $array): bool
        {
            return true;
        }
    },
]));

echo $caster->cast(['foo']) . "\n";

echo $caster->cast(['foo', 'bar']) . "\n";

echo $caster->cast(['foo', 'bar', 'baz']) . "\n";

echo $caster->cast(['foo', 'bar', 'baz', 'bim']) . "\n";

echo $caster->castTyped(['foo', 'bar', 'baz', 'bim']) . "\n";

```

**Output:**

```php
Array
(
    [0] => foo
)

I am an array!
[0 => "SURPRISE!", 1 => "bar", 2 => "baz"]
[0 => "foo", 1 => "bar", 2 => "baz", ... and 1 more element] (sample)
(array(4)) [(int) 0 => (string(3)) "foo", (int) 1 => (string(3)) "bar", (int) 2 => (string(3)) "baz", ... and 1 more element] (sample)

```

<a name="usage--type-specific-formatters--custom-object-formatter"></a>
### Custom object formatter

In this example, `\DateTimeInterface` and `\Throwable` are utilized to supply good real-world use cases.

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;

$caster = Caster::create();

$caster = $caster->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
    new class extends AbstractObjectFormatter
    {
        public function format(CasterInterface $caster, object $object): ?string
        {
            if (false === $this->isHandling($object)) {
                return null; // Pass on to next formatter or lastly DefaultObjectFormatter
            }

            assert($object instanceof DateTimeInterface);

            return sprintf(
                '%s (%s)',
                Caster::makeNormalizedClassName(new ReflectionObject($object)),
                $object->format('c'),
            );
        }

        public function isHandling(object $object): bool
        {
            return ($object instanceof DateTimeInterface);
        }
    },
    new class extends AbstractObjectFormatter
    {
        public function format(CasterInterface $caster, object $object): ?string
        {
            if (false === $this->isHandling($object)) {
                return null; // Pass on to next formatter or lastly DefaultObjectFormatter
            }

            assert($object instanceof Throwable);

            return sprintf(
                '%s {$code = %s, $file = %s, $line = %s, $message = %s}',
                Caster::makeNormalizedClassName(new ReflectionObject($object)),
                $caster->cast($object->getCode()),
                $caster->cast('.../' . basename($object->getFile())),
                $caster->cast($object->getLine()),
                $caster->cast($object->getMessage()),
            );
        }

        public function isHandling(object $object): bool
        {
            return ($object instanceof Throwable);
        }
    },
]));

echo $caster->cast(new stdClass()) . "\n";

echo $caster->cast(new DateTimeImmutable('2019-01-01T00:00:00+00:00')) . "\n";

echo $caster->cast(new RuntimeException('test', 1)) . "\n";

```

**Output:**

```php
\stdClass
\DateTimeImmutable (2019-01-01T00:00:00+00:00)
\RuntimeException {$code = 1, $file = ".../example-custom-object-formatter.php", $line = 68, $message = "test"}

```

<a name="usage--type-specific-formatters--custom-resource-formatter"></a>
### Custom resource formatter

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractResourceFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ResourceFormatterCollection;
use Eboreum\Caster\Common\DataType\Resource_;
use Eboreum\Caster\Contract\CasterInterface;

$caster = Caster::create();

$caster = $caster->withCustomResourceFormatterCollection(new ResourceFormatterCollection([
    new class extends AbstractResourceFormatter
    {
        public function format(CasterInterface $caster, Resource_ $resource): ?string
        {
            if (false === $this->isHandling($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }

            if ('stream' === get_resource_type($resource->getResource())) {
                return sprintf(
                    'opendir/fopen/tmpfile/popen/fsockopen/pfsockopen %s',
                    preg_replace(
                        '/^(Resource id) #\d+$/',
                        '$1 #42',
                        (string)$resource->getResource(),
                    ),
                );
            }

            return null; // Pass on to next formatter or lastly DefaultResourceFormatter
        }
    },
    new class extends AbstractResourceFormatter
    {
        public function format(CasterInterface $caster, Resource_ $resource): ?string
        {
            if (false === $this->isHandling($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }

            if ('xml' === get_resource_type($resource->getResource())) {
                $identifier = preg_replace(
                    '/^(Resource id) #\d+$/',
                    '$1 #42',
                    (string)$resource->getResource(),
                );

                assert(is_string($identifier));

                return sprintf(
                    'XML %s',
                    $identifier,
                );
            }

            return null; // Pass on to next formatter or lastly DefaultResourceFormatter
        }
    },
]));

echo $caster->cast(fopen(__FILE__, 'r+')) . "\n";

```

**Output:**

```php
opendir/fopen/tmpfile/popen/fsockopen/pfsockopen Resource id #42

```

<a name="usage--type-specific-formatters--custom-string-formatter"></a>
### Custom string formatter

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractStringFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\StringFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;

$caster = Caster::create();
$caster = $caster->withCustomStringFormatterCollection(new StringFormatterCollection([
    new class extends AbstractStringFormatter
    {
        public function format(CasterInterface $caster, string $string): ?string
        {
            if (false === $this->isHandling($string)) {
                return null; // Pass on to next formatter or lastly DefaultStringFormatter
            }

            if ('What do we like?' === (string)$string) {
                return $caster->cast('CAKE!');
            }

            return null; // Pass on to next formatter or lastly DefaultStringFormatter
        }

        public function isHandling(string $string): bool
        {
            return true;
        }
    },
]));

echo $caster->cast('What do we like?') . "\n";

echo $caster->castTyped('Mmmm, cake') . "\n";

```

**Output:**

```php
"CAKE!"
(string(10)) "Mmmm, cake"

```

<a name="usage--type-specific-formatters--hiding-sensitive-substrings"></a>
### Hiding sensitive substrings

You may hide sensitive strings such as passwords, authentication tokens, social security numbers, and similar.

In the example below, notice how "345" and "456" overlap, causing the product of these strings, "3456", to be masked. While this can potentially reveal, that one sensitive string is part of another, it is a lesser evil compared to masking out only one of them, and then revealing the remainder of the second sensitive string in the plain text output.

Will mask out in the order: Longest sensitive string to shortest. Meaning, with sensitive strings `"foo"` and `"foobar"`, `"foobar"` is handled first. This string, `"foobarbaz"`, will become `"******baz"`, and `"foo"` in this case is never handled. The string `"foob foobarbaz"` will become `"******b ******baz"`, and so on.

The sensitive strings are **encrypted** (IV and salt are randomized at runtime), such that if Eboreum/Caster should fail internally, it will not reveal the sensitive strings in clear text.

When using `\Eboreum\Caster\Caster->cast(...)`, any string is masked before it is passed on to a string formatter (a class implementing `StringFormatterInterface`).

**Example:**

```php
<?php

declare(strict_types=1);

use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\EncryptedStringCollection;
use Eboreum\Caster\EncryptedString;

$caster = Caster::create();
$caster = $caster->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
    new EncryptedString('bar'),
    new EncryptedString('bim'),
    new EncryptedString('345'),
    new EncryptedString('456'),
]));

echo $caster->castTyped('foo bar baz bim bum') . "\n"; // Notice: Original string length is not revealed

echo "\n\n";

echo $caster->castTyped('0123456789') . "\n"; // Notice: 3456 are masked because 345 and 456 overlap

```

**Output:**

```php
(string(25)) "foo ****** baz ****** bum" (masked)


(string(12)) "012******789" (masked)

```

# Tests

## Test/development requirements

```json
"beberlei/assert": "^3.3",
"nikic/php-parser": "^5.0",
"phpstan/phpstan": "1.11.5",
"phpunit/phpunit": "11.2.5",
"slevomat/coding-standard": "8.15.0",
"squizlabs/php_codesniffer": "3.10.1"
```

## Running tests

For all unit tests, first follow these steps:

```
cd tests
php ../vendor/bin/phpunit
```

# PHPStan

## Suppression codes

For a few cases, we need to suppress the PHPStan output, for various reasons. We strive to avoid `@phpstan-ignore-line` (and `@phpstan-ignore-next-line`, and similar), but in very few cases – primarily in tests – this is just not possible, as the very thing we test for is something PHPStan does not like.

|Code|Remark|
|-|-|
|babdc1d2|A property is never read, only written. See: [https://phpstan.org/developing-extensions/always-read-written-properties](https://phpstan.org/developing-extensions/always-read-written-properties). For tests, where the existence of such properties is integral to the tests, PHPStan shouldn't show it as an error. Sometimes, it is because a property is read through the Reflection API and not directly accessed, which confuses PHPStan.|
|136348fe|False positive by PHPStan on the error: "Dead catch - Exception is never thrown in the try block."|
|03dec37a|On-purpose testing for an invalid argument in a test, which **is** the very test, and as such, PHPStan should not report on it.|

# License & Disclaimer

See [`LICENSE`](LICENSE) file. Basically: Use this library at your own risk.

# Contributing

We prefer that you create a ticket and or a pull request at https://github.com/eboreum/caster, and have a discussion about a feature or bug here.

Please do **not** require https://packagist.org/packages/eboreum/exceptional back into this project. We do not want a bidirectional dependency as eboreum/exceptional utilizes eboreum/caster.

# Credits

## Authors

- **Kasper Søfren** (kafoso)<br>E-mail: <a href="mailto:soefritz@gmail.com">soefritz@gmail.com</a><br>Homepage: <a href="https://github.com/kafoso">https://github.com/kafoso</a>
- **Carsten Jørgensen** (corex)<br>E-mail: <a href="mailto:dev@corex.dk">dev@corex.dk</a><br>Homepage: <a href="https://github.com/corex">https://github.com/corex</a>

## Acknowledgements

Originates from and replaces: https://packagist.org/packages/kafoso/type-formatter (https://github.com/kafoso/type-formatter).
