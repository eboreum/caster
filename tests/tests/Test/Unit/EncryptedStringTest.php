<?php

declare(strict_types = 1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\EncryptedString;
use Eboreum\Caster\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class EncryptedStringTest extends TestCase
{
    /**
     * @dataProvider dataProvider_testBasics
     */
        public function testBasics(
        string $expectedEncryptionMethod,
        string $value,
        ?string $salt,
        ?string $encryptionMethod
    ): void
    {
        $encryptedString = new EncryptedString($value, $salt, $encryptionMethod);
        $this->assertInstanceOf(EncryptedString::class, $encryptedString);
        $this->assertSame($value, $encryptedString->decrypt());
        $this->assertSame($expectedEncryptionMethod, $encryptedString->getEncryptionMethod());

        $reflectionObject = new \ReflectionObject($encryptedString);
        $reflectionProperty = $reflectionObject->getProperty("encryptedString");
        $reflectionProperty->setAccessible(true);
        $encryptedString = $reflectionProperty->getValue($encryptedString);
        $this->assertNotSame($value, $encryptedString);
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: null|string, 3: null|string}>
     */
    public function dataProvider_testBasics(): array
    {
        return [
            [
                EncryptedString::ENCRYPTION_METHOD_DEFAULT,
                "foo",
                null,
                null,
            ],
            [
                EncryptedString::ENCRYPTION_METHOD_DEFAULT,
                "foo",
                "bar",
                null,
            ],
            [
                "aes-128-cbc",
                "foo",
                null,
                "aes-128-cbc",
            ],
        ];
    }

    public function testConstructorThrowsExceptionWhenArgumentSaltIsInvalid(): void
    {
        try {
            new EncryptedString("foo", "");
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertSame(
                sprintf(
                    implode("", [
                        "Failed to construct \\%s with arguments {",
                            "\$string = ** HIDDEN **",
                            ", \$salt = ** HIDDEN **",
                            ", \$encryptionMethod = (null) null",
                        "}",
                    ]),
                    EncryptedString::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertSame(
                'Argument $salt must not be an empty string, but it is. Found: (string(0)) ""',
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(is_null($currentException));

            return;
        }

        $this->fail("Exception was never thrown.");
    }

    public function testConstructorThrowsExceptionWhenArgumentEncryptionMethodIsInvalid(): void
    {
        try {
            new EncryptedString("foo", "bar", "fc1a05ff-c80c-45bd-a1a4-e1d8105881bc");
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertSame(
                sprintf(
                    implode("", [
                        "Failed to construct \\%s with arguments {",
                            "\$string = ** HIDDEN **",
                            ", \$salt = ** HIDDEN **",
                            ", \$encryptionMethod = (string(36)) \"fc1a05ff-c80c-45bd-a1a4-e1d8105881bc\"",
                        "}",
                    ]),
                    EncryptedString::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Expects argument \$encryptionMethod to be null or when a string, to be one of \[',
                            '"[^"]+"(, "[^"]+")*',
                        '\], but it is not\.',
                        ' Found: \(string\(36\)\) "fc1a05ff-c80c-45bd-a1a4-e1d8105881bc"',
                        '$',
                        '/',
                    ]),
                    EncryptedString::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(is_null($currentException));

            return;
        }

        $this->fail("Exception was never thrown.");
    }

    public function testWithEncryptionMethodWorks(): void
    {
        $encryptedStringA = new EncryptedString("foo");

        $encryptedStringB = $encryptedStringA->withEncryptionMethod("aes-128-cbc");

        $this->assertNotSame($encryptedStringA, $encryptedStringB);
        $this->assertSame(EncryptedString::ENCRYPTION_METHOD_DEFAULT, $encryptedStringA->getEncryptionMethod());
        $this->assertSame("foo", $encryptedStringA->decrypt());
        $this->assertSame("aes-128-cbc", $encryptedStringB->getEncryptionMethod());
        $this->assertSame("foo", $encryptedStringB->decrypt());
    }

    public function testWithEncryptionMethodThrowsExceptionWhenArgumentExceptionMethodIsInvalid(): void
    {
        $encryptedString = new EncryptedString("foo");

        try {
            $encryptedString->withEncryptionMethod("fc75493b-e598-4417-a255-c054268c4449");
        } catch (\Exception $e) {
            $currentException = $e;
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertSame(
                sprintf(
                    implode("", [
                        "Failure in \\%s->withEncryptionMethod(",
                            "\$encryptionMethod = (string(36)) \"fc75493b-e598-4417-a255-c054268c4449\"",
                        "): (object) \\%s",
                    ]),
                    EncryptedString::class,
                    EncryptedString::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertSame(RuntimeException::class, get_class($currentException));
            $this->assertMatchesRegularExpression(
                sprintf(
                    implode("", [
                        '/',
                        '^',
                        'Expects argument \$encryptionMethod to be one of \[',
                            '"[^"]+"(, "[^"]+")*',
                        '\], but it is not\.',
                        ' Found: \(string\(36\)\) "fc75493b-e598-4417-a255-c054268c4449"',
                        '$',
                        '/',
                    ]),
                    EncryptedString::class,
                ),
                $currentException->getMessage(),
            );

            $currentException = $currentException->getPrevious();
            $this->assertTrue(is_null($currentException));

            return;
        }

        $this->fail("Exception was never thrown.");
    }

    public function testGenerateRandomSaltWorks(): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{128}$/',
            EncryptedString::generateRandomSalt(),
        );
    }
}
