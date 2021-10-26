<?php

declare(strict_types=1);

namespace Eboreum\Caster;

use Eboreum\Caster\Contract\Collection\ElementInterface;
use Eboreum\Caster\Contract\ImmutableObjectInterface;
use Eboreum\Caster\Exception\RuntimeException;
use Exception;

/**
 * {@inheritDoc}
 *
 * Stores a string, e.g. a password, as an encrypted value.
 * Used with Caster to prevent outputting of sensitive information.
 */
class EncryptedString implements ImmutableObjectInterface, ElementInterface
{
    public const ENCRYPTION_METHOD_DEFAULT = 'aes-256-cbc';

    protected string $initializationVectorBase;

    protected string $salt;

    protected string $encryptionMethod;

    protected string $encryptedString;

    /**
     * @throws RuntimeException
     */
    public function __construct(string $string, ?string $salt = null, ?string $encryptionMethod = null)
    {
        try {
            $errorMessages = [];

            $saltVariation = $salt;

            if (null === $saltVariation) {
                $saltVariation = static::generateRandomSalt();
            } else {
                if ('' === $saltVariation) {
                    $errorMessages[] = sprintf(
                        'Argument $salt must not be an empty string, but it is. Found: %s',
                        Caster::getInternalInstance()->castTyped($salt),
                    );
                }
            }

            $encryptionMethodVariant = $encryptionMethod;

            if (null === $encryptionMethodVariant) {
                $encryptionMethodVariant = self::ENCRYPTION_METHOD_DEFAULT;
            }

            if (false === static::isEncryptionMethodValid($encryptionMethodVariant)) {
                $errorMessages[] = sprintf(
                    implode('', [
                        'Expects argument $encryptionMethod to be null or when a string, to be one of [%s]',
                        ', but it is not. Found: %s',
                    ]),
                    implode(
                        ', ',
                        array_map(
                            static function ($v) {
                                return Caster::getInternalInstance()->cast($v);
                            },
                            \openssl_get_cipher_methods(),
                        ),
                    ),
                    Caster::getInternalInstance()->castTyped($encryptionMethod),
                );
            }

            if ($errorMessages) {
                throw new RuntimeException(implode('. ', $errorMessages));
            }

            $this->salt = $saltVariation;
            $this->encryptionMethod = $encryptionMethodVariant;
            $this->initializationVectorBase = self::generateRandomSalt() . \spl_object_hash($this);
            $encryptedString = \openssl_encrypt(
                $string,
                $this->encryptionMethod,
                $this->salt,
                0,
                $this->getInitializationVector()
            );

            assert(is_string($encryptedString));

            $this->encryptedString = $encryptedString;
        } catch (\Throwable $t) {
            $argumentSegments = [];
            $argumentSegments[] = '$string = ** HIDDEN **';
            $argumentSegments[] = '$salt = ** HIDDEN **';
            $argumentSegments[] = sprintf(
                '$encryptionMethod = %s',
                Caster::getInternalInstance()->castTyped($encryptionMethod),
            );

            throw new RuntimeException(sprintf(
                'Failed to construct \\%s with arguments {%s}',
                static::class,
                implode(', ', $argumentSegments),
            ), 0, $t);
        }
    }

    /**
     * Decrypts the value, making it readable in clear text (memory) yet again, and returns it.
     */
    public function decrypt(): string
    {
        $decrypted = \openssl_decrypt(
            $this->encryptedString,
            $this->encryptionMethod,
            $this->salt,
            0,
            $this->getInitializationVector(),
        );

        assert(is_string($decrypted));

        return $decrypted;
    }

    /**
     * @throws RuntimeException
     */
    public function withEncryptionMethod(string $encryptionMethod): EncryptedString
    {
        try {
            $errorMessages = [];

            if (false === static::isEncryptionMethodValid($encryptionMethod)) {
                $errorMessages[] = sprintf(
                    'Expects argument $encryptionMethod to be one of [%s], but it is not. Found: %s',
                    implode(
                        ', ',
                        array_map(
                            static function ($encryptionMethod) {
                                return Caster::getInternalInstance()->cast($encryptionMethod);
                            },
                            \openssl_get_cipher_methods()
                        ),
                    ),
                    Caster::getInternalInstance()->castTyped($encryptionMethod),
                );
            }

            if ($errorMessages) {
                throw new RuntimeException(implode('. ', $errorMessages));
            }

            $clone = clone $this;
            $clone->encryptionMethod = $encryptionMethod;
            $clone->initializationVectorBase = self::generateRandomSalt() . \spl_object_hash($clone);
            $encryptedString = \openssl_encrypt(
                $this->decrypt(),
                $clone->encryptionMethod,
                $clone->salt,
                0,
                $clone->getInitializationVector(),
            );

            assert(is_string($encryptedString));

            $clone->encryptedString = $encryptedString;
        } catch (\Throwable $t) {
            $argumentsAsStrings = [];
            $argumentsAsStrings[] = sprintf(
                '$encryptionMethod = %s',
                Caster::getInternalInstance()->castTyped($encryptionMethod),
            );

            throw new RuntimeException(sprintf(
                'Failure in %s->%s(%s): %s',
                Caster::makeNormalizedClassName(new \ReflectionObject($this)),
                __FUNCTION__,
                implode(', ', $argumentsAsStrings),
                Caster::getInternalInstance()->castTyped($this),
            ), 0, $t);
        }

        return $clone;
    }

    public function getEncryptionMethod(): string
    {
        return $this->encryptionMethod;
    }

    protected function getInitializationVector(): string
    {
        return substr(\hash('sha256', $this->initializationVectorBase), 0, 16);
    }

    /**
     * @throws Exception
     */
    public static function generateRandomSalt(): string
    {
        return \bin2hex(\random_bytes(64));
    }

    public static function isEncryptionMethodValid(string $encryptionMethod): bool
    {
        return in_array($encryptionMethod, \openssl_get_cipher_methods(), true);
    }
}
