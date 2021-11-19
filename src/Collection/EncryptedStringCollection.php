<?php

declare(strict_types=1);

namespace Eboreum\Caster\Collection;

use Eboreum\Caster\Abstraction\Collection\AbstractObjectCollection;
use Eboreum\Caster\EncryptedString;
use Eboreum\Caster\Exception\RuntimeException;

class EncryptedStringCollection extends AbstractObjectCollection
{
    /** @var array<int, EncryptedString> */
    protected array $elements;

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function __construct(EncryptedString ...$elements)
    {
        parent::__construct(...$elements);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledClassName(): string
    {
        return EncryptedString::class;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, EncryptedString>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-ignore-next-line
     * @return \ArrayIterator<int, EncryptedString>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }
}
