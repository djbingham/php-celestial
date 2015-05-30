<?php
namespace Sloth\Module\Resource\Base;

use Sloth\Module\Resource\Base\Resource as BaseResource;
use Sloth\Module\Resource\QueryFactory;
use SlothMySql\DatabaseWrapper;

interface ResourceFactory
{
    /**
     * @param ResourceDefinition $definition
     * @param QueryFactory $queryFactory
     */
    public function __construct(ResourceDefinition $definition, QueryFactory $queryFactory);

	/**
	 * @return ResourceDefinition
	 */
	public function getDefinition();

    /**
     * Fetch resources form the database whose attributes exactly match the supplied values
     * @param array $attributes
     * @return ResourceList
     */
    public function getBy(array $attributes);

    /**
     * Search the database for resources matching the supplied filters and options as well as this factory's manifest
     * @param array $filters
     * @return ResourceList
     */
    public function search(array $filters);

    /**
     * Create a new Resource with supplied attribute values
     * @param array $attributes
     * @return Resource
     */
    public function create(array $attributes);

    /**
     * Save updates to a given Resource
     * @param BaseResource $resource
     * @return BaseResource
     */
    public function update(BaseResource $resource);

    /**
     * Delete a given BaseResource
     * @param BaseResource $resource
     * @return $this
     */
    public function delete(BaseResource $resource);
}