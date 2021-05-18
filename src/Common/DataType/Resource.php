<?php

declare(strict_types=1);

namespace Eboreum\Caster\Common\DataType;

use Eboreum\Caster\Caster;

/**
 * Contains a resource.
 *
 * PHP does not have a "resource" type hint.
 *
 * @see https://wiki.php.net/rfc/scalar_type_hints#type_hint_choices
 *
 * To be able to utilize type hints for resources, this wrapper class was implemented.
 */
class Resource
{
    /**
     * A resource.
     */
    protected $resource;

    /**
     * Argument $resource must be a resource.
     * @throws \InvalidArgumentException
     */
    public function __construct($resource)
    {
        if (false === is_resource($resource)) {
            throw new \InvalidArgumentException(sprintf(
                "Expects argument \$resource to be a resource, but it is not. Found: %s",
                Caster::getInternalInstance()->castTyped($resource),
            ));
        }

        $this->resource = $resource;
    }

    /**
     * Returns a resource.
     */
    public function getResource()
    {
        return $this->resource;
    }
}
