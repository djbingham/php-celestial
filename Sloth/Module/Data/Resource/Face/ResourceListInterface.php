<?php

namespace Sloth\Module\Data\Resource\Face;

use Sloth\Module\Data\Resource\Face\ResourceInterface;

interface ResourceListInterface extends ResourceInterface, \Iterator
{
    /**
     * Append a new Resource to this list, with given attributeList
     * @param ResourceInterface $resource
     * @return $this
     */
    public function push(ResourceInterface $resource);

    /**
     * Append several new Resources to this list, with given sets of attributeList
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
     * Prepend a new Resource to this list, with given attributeList
     * @param ResourceInterface $resource
     * @return $this
     */
    public function unshift(ResourceInterface $resource);

    /**
     * Append several new Resources to this list, with given sets of attributeList
     * @param array $resources
     * @return $this
     */
    public function unshiftMany(array $resources);

    /**
     * Returns the Resource at a given index
     * @param int $index
     * @return ResourceInterface
     */
    public function getByIndex($index);

    /**
     * Returns the number of resources currently in the list
     * @return int
     */
    public function count();
}