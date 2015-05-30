<?php

namespace Sloth\Module\Resource\Base;

use Sloth\Module\Resource\Base\Resource as BaseResource;

interface ResourceList extends BaseResource
{
    /**
     * Append a new Resource to this list, with given attributes
     * @param BaseResource $resource
     * @return $this
     */
    public function push(BaseResource $resource);

    /**
     * Append several new Resources to this list, with given sets of attributes
     * @param array $resources
     * @return $this
     */
    public function pushMany(array $resources);

    /**
     * Remove the last Resource (or several) from this list
     * @param int $quantity
     * @return $this
     */
    public function pop($quantity = 1);

    /**
     * Remove the first Resource (or several) from this list
     * @param int $quantity
     * @return $this
     */
    public function shift($quantity = 1);

    /**
     * Prepend a new Resource to this list, with given attributes
     * @param BaseResource $resource
     * @return $this
     */
    public function unshift(BaseResource $resource);

    /**
     * Append several new Resources to this list, with given sets of attributes
     * @param array $resources
     * @return $this
     */
    public function unshiftMany(array $resources);

    /**
     * Returns the Resource at a given index
     * @param int $index
     * @return BaseResource
     */
    public function get($index);

    /**
     * Returns the number of resources currently in the list
     * @return int
     */
    public function count();
}