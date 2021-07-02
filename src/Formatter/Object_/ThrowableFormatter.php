<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Formatter\Object_;

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Common\DataType\Integer\PositiveInteger;
use Eboreum\Caster\Common\DataType\Integer\UnsignedInteger;
use Eboreum\Caster\Contract\CasterInterface;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Formatter\DefaultObjectFormatter;

/**
 * Formats instances of \Throwable.
 */
class ThrowableFormatter extends AbstractObjectFormatter
{
    protected PositiveInteger $depthMaximum;

    protected UnsignedInteger $messageMaximumLength;

    public function __construct()
    {
        $this->depthMaximum = new PositiveInteger(100);
        $this->messageMaximumLength = new UnsignedInteger(5000);
    }

    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, object $object): ?string
    {
        if (false === $this->isHandling($object)) {
            return null; // Pass on
        }

        assert($object instanceof \Throwable);

        if (1 === $caster->getContext()->count()) {
            /*
             * Don't omit previous throwables after rather few of them (e.g. merely 3).
             */
            $caster = $caster->withDepthMaximum($this->getDepthMaximum());
        }

        $casterMessage = $caster
            ->withStringSampleSize(clone $this->getMessageMaximumLength());

        return sprintf(
            "%s {\$code = %s, \$file = %s, \$line = %s, \$message = %s, \$previous = %s}",
            Caster::makeNormalizedClassName(new \ReflectionObject($object)),
            $caster->cast($object->getCode()),
            $caster->cast($object->getFile()),
            $caster->cast($object->getLine()),
            $casterMessage->cast($object->getMessage()),
            $caster->cast($object->getPrevious()),
        );
    }

    public function withDepthMaximum(PositiveInteger $depthMaximum): ThrowableFormatter
    {
        $clone = clone $this;
        $clone->depthMaximum = $depthMaximum;

        return $clone;
    }

    public function withMessageMaximumLength(UnsignedInteger $messageMaximumLength): ThrowableFormatter
    {
        $clone = clone $this;
        $clone->messageMaximumLength = $messageMaximumLength;

        return $clone;
    }

    public function getDepthMaximum(): PositiveInteger
    {
        return $this->depthMaximum;
    }

    public function getMessageMaximumLength(): UnsignedInteger
    {
        return $this->messageMaximumLength;
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(object $object): bool
    {
        return ($object instanceof \Throwable);
    }
}
