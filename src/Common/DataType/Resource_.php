<?php

declare(strict_types=1);

namespace Eboreum\Caster\Common\DataType;

use Eboreum\Caster\Caster;
use InvalidArgumentException;

use function is_resource;
use function sprintf;

/**
 * Contains a resource.
 *
 * PHP does not have a "resource" type hint.
 *
 * @see https://wiki.php.net/rfc/scalar_type_hints#type_hint_choices
 *
 * To be able to utilize type hints for resources, this wrapper class was implemented.
 */
class Resource_ // phpcs:ignore
{
    /** @var resource $resource */
    protected $resource;

    /**
     * Argument $resource must be a resource.
     *
     * @param resource $resource
     *
     * @throws InvalidArgumentException
     */
    public function __construct($resource)
    {
        if (! is_resource($resource)) {
            throw new InvalidArgumentException(sprintf(
                'Expects argument $resource to be a resource, but it is not. Found: %s',
                Caster::getInternalInstance()->castTyped($resource),
            ));
        }

        $this->resource = $resource;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
