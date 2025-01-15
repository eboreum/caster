<?php

declare(strict_types=1);

namespace Eboreum\Caster;

/**
 * A special class used to redact values.
 *
 * When caster receives an instance of this class (a singleton), it will produce the redaction message and NEVER show
 * any of the original value.
 *
 * To be used with:
 *
 *   - \SensitiveParameter
 *   - \Eboreum\Caster\Attribute\SensitiveProperty
 */
final class SensitiveValue
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }
}
